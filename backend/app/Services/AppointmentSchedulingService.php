<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\RiskLevel;
use App\Jobs\NotifyHighRiskAppointmentJob;
use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AppointmentSchedulingService
{
    public function __construct(
        protected NoShowPredictionService $predictionService,
        protected AuditLoggerService $auditLogger
    ) {}

    public function book(
        Patient $patient,
        Doctor $doctor,
        Carbon|string $scheduledStart,
        Carbon|string $scheduledEnd,
        string $reason,
        AppointmentType $type = AppointmentType::TELEHEALTH,
        ?string $notes = null
    ): Appointment {
        $start = Carbon::parse($scheduledStart);
        $end = Carbon::parse($scheduledEnd);

        $this->validateAppointmentTime($start, $end);
        $this->validateDoctorWorkingHours($doctor, $start, $end);
        $this->validateDoctorAvailabilityOverlap($doctor->id, $start, $end);
        $this->validatePatientAvailabilityOverlap($patient->id, $start, $end);

        $meetingLink = null;
        if ($type === AppointmentType::TELEHEALTH) {
            $meetingLink = 'https://meet.medicon.health/room/' . bin2hex(random_bytes(8));
        }

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => $start,
            'scheduled_end' => $end,
            'status' => AppointmentStatus::CONFIRMED,
            'type' => $type,
            'reason' => $reason,
            'notes' => $notes,
            'meeting_link' => $meetingLink,
            'is_reminder_sent' => false,
        ]);

        // Compute ML risk score
        $prediction = $this->predictionService->predictForAppointment($appointment);
        $appointment->update([
            'no_show_risk_score' => $prediction['score'],
            'no_show_risk_level' => $prediction['level'],
            'risk_factors' => $prediction['factors'],
        ]);

        // Dispatch background jobs
        if ($prediction['is_high_risk']) {
            NotifyHighRiskAppointmentJob::dispatch($appointment);
        }

        // Schedule reminder job 24h prior if appointment is far enough
        if ($start->diffInHours(now()) >= 24) {
            SendAppointmentReminderJob::dispatch($appointment)
                ->delay($start->copy()->subHours(24));
        }

        $this->auditLogger->logCreate($appointment, $patient->id);

        return $appointment->fresh(['patient.user', 'doctor.user']);
    }

    public function reschedule(
        Appointment $appointment,
        Carbon|string $newStart,
        Carbon|string $newEnd,
        ?string $reason = null
    ): Appointment {
        if (!$appointment->isCancellable()) {
            throw ValidationException::withMessages([
                'appointment' => ['Completed, cancelled, or past appointments cannot be rescheduled.'],
            ]);
        }

        $start = Carbon::parse($newStart);
        $end = Carbon::parse($newEnd);

        $this->validateAppointmentTime($start, $end);
        $this->validateDoctorWorkingHours($appointment->doctor, $start, $end);
        $this->validateDoctorAvailabilityOverlap($appointment->doctor_id, $start, $end, $appointment->id);
        $this->validatePatientAvailabilityOverlap($appointment->patient_id, $start, $end, $appointment->id);

        $original = $appointment->getAttributes();

        $appointment->scheduled_start = $start;
        $appointment->scheduled_end = $end;
        if ($reason) {
            $appointment->notes = ($appointment->notes ? $appointment->notes . "\n" : "") . "[Rescheduled]: " . $reason;
        }

        // Re-evaluate no-show risk with new lead time
        $prediction = $this->predictionService->predictForAppointment($appointment);
        $appointment->no_show_risk_score = $prediction['score'];
        $appointment->no_show_risk_level = $prediction['level'];
        $appointment->risk_factors = $prediction['factors'];
        $appointment->save();

        $this->auditLogger->logUpdate($appointment, $original, $appointment->patient_id);

        return $appointment->fresh(['patient.user', 'doctor.user']);
    }

    public function cancel(Appointment $appointment, string $cancellationReason): Appointment
    {
        if ($appointment->status->isTerminal()) {
            throw ValidationException::withMessages([
                'appointment' => ['Appointment is already completed or cancelled.'],
            ]);
        }

        $original = $appointment->getAttributes();

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
            'cancellation_reason' => $cancellationReason,
        ]);

        $this->auditLogger->logUpdate($appointment, $original, $appointment->patient_id);

        return $appointment;
    }

    protected function validateAppointmentTime(Carbon $start, Carbon $end): void
    {
        if ($start->isPast()) {
            throw ValidationException::withMessages([
                'scheduled_start' => ['Appointment time cannot be in the past.'],
            ]);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'scheduled_end' => ['Appointment end time must be after the start time.'],
            ]);
        }

        $durationMinutes = $start->diffInMinutes($end);
        if ($durationMinutes < 15 || $durationMinutes > 120) {
            throw ValidationException::withMessages([
                'scheduled_end' => ['Appointment duration must be between 15 minutes and 2 hours.'],
            ]);
        }
    }

    protected function validateDoctorWorkingHours(Doctor $doctor, Carbon $start, Carbon $end): void
    {
        $dayOfWeek = (int) $start->dayOfWeek; // 0=Sunday, 6=Saturday
        $startTimeString = $start->format('H:i:s');
        $endTimeString = $end->format('H:i:s');

        $hasAvailability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('start_time', '<=', $startTimeString)
            ->where('end_time', '>=', $endTimeString)
            ->exists();

        if (!$hasAvailability) {
            throw ValidationException::withMessages([
                'scheduled_start' => ['The selected time slot falls outside of the doctor\'s configured working hours.'],
            ]);
        }
    }

    public function validateDoctorAvailabilityOverlap(int $doctorId, Carbon $start, Carbon $end, ?int $ignoreAppointmentId = null): void
    {
        $query = Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED, AppointmentStatus::IN_PROGRESS])
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($sub) use ($start, $end) {
                    $sub->where('scheduled_start', '<', $end)
                        ->where('scheduled_end', '>', $start);
                });
            });

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'scheduled_start' => ['The doctor already has an appointment scheduled during this time slot.'],
            ]);
        }
    }

    public function validatePatientAvailabilityOverlap(int $patientId, Carbon $start, Carbon $end, ?int $ignoreAppointmentId = null): void
    {
        $query = Appointment::where('patient_id', $patientId)
            ->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED, AppointmentStatus::IN_PROGRESS])
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($sub) use ($start, $end) {
                    $sub->where('scheduled_start', '<', $end)
                        ->where('scheduled_end', '>', $start);
                });
            });

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'scheduled_start' => ['You already have another appointment scheduled during this time slot.'],
            ]);
        }
    }
}
