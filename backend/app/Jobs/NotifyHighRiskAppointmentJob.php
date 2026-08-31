<?php

namespace App\Jobs;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyHighRiskAppointmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public Appointment $appointment)
    {
        $this->onQueue('high-priority');
    }

    public function handle(): void
    {
        $appointment = $this->appointment->fresh(['patient.user', 'doctor.user']);
        
        if (!$appointment) {
            return;
        }

        Log::info("FLAGGED_HIGH_RISK_APPOINTMENT_ALERT", [
            'appointment_id' => $appointment->id,
            'risk_score' => $appointment->no_show_risk_score,
            'risk_level' => $appointment->no_show_risk_level?->value,
            'risk_factors' => $appointment->risk_factors,
            'patient' => $appointment->patient?->user?->name,
            'doctor' => $appointment->doctor?->user?->name,
            'scheduled_start' => $appointment->scheduled_start->toIso8601String(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("FAILED_HIGH_RISK_NOTIFICATION_JOB", [
            'appointment_id' => $this->appointment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
