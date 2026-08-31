<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Enums\RiskLevel;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::where('is_active', true)->count();
        $totalAppointments = Appointment::count();
        
        $completedAppointments = Appointment::where('status', AppointmentStatus::COMPLETED)->count();
        $noShowAppointments = Appointment::where('status', AppointmentStatus::NO_SHOW)->count();
        $cancelledAppointments = Appointment::where('status', AppointmentStatus::CANCELLED)->count();

        $noShowRate = ($completedAppointments + $noShowAppointments) > 0
            ? round(($noShowAppointments / ($completedAppointments + $noShowAppointments)) * 100, 1)
            : 0.0;

        $highRiskCount = Appointment::where('no_show_risk_level', RiskLevel::HIGH)
            ->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED])
            ->where('scheduled_start', '>=', now())
            ->count();

        // Estimated revenue from completed visits
        $totalRevenue = Appointment::where('status', AppointmentStatus::COMPLETED)
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->sum('doctors.consultation_fee');

        // Risk distribution
        $riskDistribution = [
            'LOW' => Appointment::where('no_show_risk_level', RiskLevel::LOW)->count(),
            'MEDIUM' => Appointment::where('no_show_risk_level', RiskLevel::MEDIUM)->count(),
            'HIGH' => Appointment::where('no_show_risk_level', RiskLevel::HIGH)->count(),
        ];

        // Appointments per month for the last 6 months
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $monthlyAppointments = Appointment::whereBetween('scheduled_start', [$monthStart, $monthEnd])->count();
            $monthlyCompleted = Appointment::whereBetween('scheduled_start', [$monthStart, $monthEnd])
                ->where('status', AppointmentStatus::COMPLETED)->count();
            $monthlyNoShows = Appointment::whereBetween('scheduled_start', [$monthStart, $monthEnd])
                ->where('status', AppointmentStatus::NO_SHOW)->count();

            $monthlyTrends[] = [
                'month' => $month->format('M Y'),
                'total' => $monthlyAppointments,
                'completed' => $monthlyCompleted,
                'no_shows' => $monthlyNoShows,
            ];
        }

        // Doctor utilization
        $doctorUtilization = Doctor::with('user')
            ->where('is_active', true)
            ->withCount(['appointments as total_appointments'])
            ->withCount(['appointments as completed_appointments' => function ($q) {
                $q->where('status', AppointmentStatus::COMPLETED);
            }])
            ->withCount(['appointments as no_shows' => function ($q) {
                $q->where('status', AppointmentStatus::NO_SHOW);
            }])
            ->orderByDesc('total_appointments')
            ->take(10)
            ->get()
            ->map(function ($doc) {
                return [
                    'doctor_id' => $doc->id,
                    'name' => $doc->user?->name,
                    'specialty' => $doc->specialty,
                    'total_appointments' => $doc->total_appointments,
                    'completed_appointments' => $doc->completed_appointments,
                    'no_shows' => $doc->no_shows,
                    'rating' => (float) $doc->rating,
                ];
            });

        return response()->json([
            'overview' => [
                'total_patients' => $totalPatients,
                'total_doctors' => $totalDoctors,
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'no_show_rate' => $noShowRate,
                'high_risk_flagged' => $highRiskCount,
                'total_revenue' => (float) $totalRevenue,
            ],
            'risk_distribution' => $riskDistribution,
            'monthly_trends' => $monthlyTrends,
            'doctor_utilization' => $doctorUtilization,
        ]);
    }

    public function highRiskAppointments(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user'])
            ->where('no_show_risk_level', RiskLevel::HIGH)
            ->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED])
            ->where('scheduled_start', '>=', now())
            ->orderBy('scheduled_start')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => AppointmentResource::collection($appointments),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }
}
