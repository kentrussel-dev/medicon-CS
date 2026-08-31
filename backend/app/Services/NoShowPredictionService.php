<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\RiskLevel;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NoShowPredictionService
{
    protected string $serviceUrl;
    protected float $timeout;
    protected float $highRiskThreshold;
    protected float $mediumRiskThreshold;

    public function __construct()
    {
        $this->serviceUrl = config('services.ml_service.url', 'http://ml-service:8000');
        $this->timeout = (float) config('services.ml_service.timeout', 2.5);
        $this->highRiskThreshold = (float) config('services.ml_service.high_risk_threshold', 0.65);
        $this->mediumRiskThreshold = (float) config('services.ml_service.medium_risk_threshold', 0.35);
    }

    public function predictForAppointment(Appointment $appointment): array
    {
        $patient = $appointment->patient ?? Patient::find($appointment->patient_id);
        
        $scheduledStart = Carbon::parse($appointment->scheduled_start);
        $leadTimeDays = max(0, (int) now()->diffInDays($scheduledStart, false));
        
        // Compute prior appointment statistics
        $priorAppointmentsCount = 0;
        $priorNoShowsCount = 0;
        
        if ($patient) {
            $priorAppointmentsCount = Appointment::where('patient_id', $patient->id)
                ->where('id', '!=', $appointment->id ?? 0)
                ->where('scheduled_start', '<', $scheduledStart)
                ->whereIn('status', [AppointmentStatus::COMPLETED, AppointmentStatus::NO_SHOW])
                ->count();

            $priorNoShowsCount = Appointment::where('patient_id', $patient->id)
                ->where('id', '!=', $appointment->id ?? 0)
                ->where('scheduled_start', '<', $scheduledStart)
                ->where('status', AppointmentStatus::NO_SHOW)
                ->count();
        }

        $age = $patient ? $patient->calculateAge() : 35;
        $gender = ($patient && in_array(strtoupper($patient->gender), ['M', 'F'])) ? strtoupper($patient->gender) : 'F';
        $scholarship = ($patient && $patient->scholarship) ? 1 : 0;
        $hypertension = ($patient && $patient->hypertension) ? 1 : 0;
        $diabetes = ($patient && $patient->diabetes) ? 1 : 0;
        $alcoholism = ($patient && $patient->alcoholism) ? 1 : 0;
        $handicap = ($patient && $patient->handicap_level) ? min(4, $patient->handicap_level) : 0;
        $smsReceived = $appointment->is_reminder_sent ? 1 : ($leadTimeDays < 2 ? 1 : 0);
        $dayOfWeek = (int) $scheduledStart->dayOfWeekIso - 1; // 0=Monday to 6=Sunday
        $appointmentHour = (int) $scheduledStart->hour;

        $payload = [
            'appointment_id' => $appointment->id,
            'features' => [
                'lead_time_days' => $leadTimeDays,
                'age' => $age,
                'gender' => $gender,
                'scholarship' => $scholarship,
                'hypertension' => $hypertension,
                'diabetes' => $diabetes,
                'alcoholism' => $alcoholism,
                'handicap' => $handicap,
                'sms_received' => $smsReceived,
                'prior_appointments' => $priorAppointmentsCount,
                'prior_no_shows' => $priorNoShowsCount,
                'day_of_week' => $dayOfWeek,
                'appointment_hour' => $appointmentHour,
            ],
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->retry(1, 100)
                ->post("{$this->serviceUrl}/predict", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'score' => (float) $data['no_show_probability'],
                    'level' => RiskLevel::from($data['risk_level']),
                    'is_high_risk' => (bool) $data['is_high_risk'],
                    'factors' => $data['contributing_factors'] ?? [],
                    'source' => 'ml_service',
                ];
            }

            Log::warning("ML Service returned non-200 status: {$response->status()}", [
                'appointment_id' => $appointment->id,
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::warning("ML Service connection failed. Using fallback heuristic.", [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback Heuristic
        return $this->computeFallbackHeuristic(
            leadTimeDays: $leadTimeDays,
            priorAppointments: $priorAppointmentsCount,
            priorNoShows: $priorNoShowsCount,
            smsReceived: $smsReceived,
            scholarship: $scholarship,
            dayOfWeek: $dayOfWeek
        );
    }

    public function computeFallbackHeuristic(
        int $leadTimeDays,
        int $priorAppointments,
        int $priorNoShows,
        int $smsReceived,
        int $scholarship,
        int $dayOfWeek
    ): array {
        $score = 0.18; // Base rate
        $factors = [];

        if ($leadTimeDays > 14) {
            $score += min(0.35, $leadTimeDays * 0.015);
            $factors[] = "High booking lead time ({$leadTimeDays} days)";
        } elseif ($leadTimeDays > 5) {
            $score += $leadTimeDays * 0.01;
        }

        if ($priorAppointments > 0) {
            $ratio = $priorNoShows / $priorAppointments;
            if ($ratio >= 0.5) {
                $score += 0.35;
                $factors[] = "History of missed appointments ({$priorNoShows}/{$priorAppointments})";
            } elseif ($priorNoShows > 0) {
                $score += 0.15;
                $factors[] = "Prior missed appointments recorded";
            }
        } else {
            $factors[] = "New patient with no prior attendance history";
        }

        if ($smsReceived === 0 && $leadTimeDays >= 3) {
            $score += 0.12;
            $factors[] = "No SMS reminder confirmed";
        }

        if ($scholarship === 1) {
            $score += 0.08;
            $factors[] = "Welfare assistance recipient";
        }

        if (in_array($dayOfWeek, [4, 5], true)) { // Friday/Saturday
            $score += 0.05;
            $factors[] = "Weekend-adjacent appointment schedule";
        }

        $clampedScore = (float) max(0.02, min(0.95, round($score, 4)));

        if ($clampedScore >= $this->highRiskThreshold) {
            $level = RiskLevel::HIGH;
            $isHighRisk = true;
        } elseif ($clampedScore >= $this->mediumRiskThreshold) {
            $level = RiskLevel::MEDIUM;
            $isHighRisk = false;
        } else {
            $level = RiskLevel::LOW;
            $isHighRisk = false;
            if (empty($factors)) {
                $factors[] = "Strong attendance profile with low risk indicators";
            }
        }

        return [
            'score' => $clampedScore,
            'level' => $level,
            'is_high_risk' => $isHighRisk,
            'factors' => $factors,
            'source' => 'heuristic_fallback',
        ];
    }
}
