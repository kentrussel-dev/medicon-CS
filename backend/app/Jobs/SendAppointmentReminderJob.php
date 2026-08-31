<?php

namespace App\Jobs;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900]; // Exponential backoff in seconds

    public function __construct(public Appointment $appointment)
    {
        $this->onQueue('reminders');
    }

    public function handle(): void
    {
        $appointment = $this->appointment->fresh(['patient.user', 'doctor.user']);

        if (!$appointment || $appointment->status !== AppointmentStatus::CONFIRMED || $appointment->is_reminder_sent) {
            return;
        }

        $patient = $appointment->patient;
        $user = $patient?->user;

        if (!$user) {
            Log::warning("Cannot send appointment reminder: User not found for appointment {$appointment->id}");
            return;
        }

        // Simulate delivery via SMS / Email gateway
        Log::info("DISPATCHED_APPOINTMENT_REMINDER", [
            'appointment_id' => $appointment->id,
            'patient_email' => $user->email,
            'scheduled_start' => $appointment->scheduled_start->toIso8601String(),
            'doctor' => $appointment->doctor?->user?->name,
            'type' => $appointment->type->value,
        ]);

        $appointment->update([
            'is_reminder_sent' => true,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("FAILED_APPOINTMENT_REMINDER_JOB", [
            'appointment_id' => $this->appointment->id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
