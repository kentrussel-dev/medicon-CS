<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NoShowPredictionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ComputeAppointmentRiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 180];

    public function __construct(public Appointment $appointment)
    {
        $this->onQueue('ml-predictions');
    }

    public function handle(NoShowPredictionService $predictionService): void
    {
        $appointment = $this->appointment->fresh(['patient.user']);

        if (!$appointment) {
            return;
        }

        $result = $predictionService->predictForAppointment($appointment);

        $appointment->update([
            'no_show_risk_score' => $result['score'],
            'no_show_risk_level' => $result['level'],
            'risk_factors' => $result['factors'],
        ]);

        if ($result['is_high_risk']) {
            NotifyHighRiskAppointmentJob::dispatch($appointment);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("FAILED_COMPUTE_RISK_JOB", [
            'appointment_id' => $this->appointment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
