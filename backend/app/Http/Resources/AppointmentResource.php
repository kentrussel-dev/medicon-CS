<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $data = [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'patient_name' => $this->patient?->user?->name,
            'patient_email' => $this->patient?->user?->email,
            'patient_phone' => $this->patient?->user?->phone,
            'doctor_name' => $this->doctor?->user?->name,
            'doctor_specialty' => $this->doctor?->specialty,
            'doctor_fee' => (float) ($this->doctor?->consultation_fee ?? 0),
            'scheduled_start' => $this->scheduled_start?->toIso8601String(),
            'scheduled_end' => $this->scheduled_end?->toIso8601String(),
            'status' => $this->status->value,
            'type' => $this->type->value,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'meeting_link' => $this->meeting_link,
            'cancellation_reason' => $this->cancellation_reason,
            'is_reminder_sent' => $this->is_reminder_sent,
            'created_at' => $this->created_at?->toIso8601String(),
            'medical_record' => new MedicalRecordResource($this->whenLoaded('medicalRecord')),
            'prescription' => new PrescriptionResource($this->whenLoaded('prescription')),
        ];

        // Doctors and Admins can see ML risk predictions
        if ($user && ($user->isAdmin() || $user->isDoctor())) {
            $data['no_show_risk_score'] = $this->no_show_risk_score !== null ? (float) $this->no_show_risk_score : null;
            $data['no_show_risk_level'] = $this->no_show_risk_level?->value;
            $data['risk_factors'] = $this->risk_factors ?? [];
        }

        return $data;
    }
}
