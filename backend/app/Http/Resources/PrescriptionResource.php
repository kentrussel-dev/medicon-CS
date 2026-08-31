<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_id' => $this->appointment_id,
            'patient_name' => $this->patient?->user?->name,
            'doctor_name' => $this->doctor?->user?->name,
            'doctor_license' => $this->doctor?->license_number,
            'notes' => $this->notes,
            'is_dispensed' => $this->is_dispensed,
            'valid_until' => $this->valid_until?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => PrescriptionItemResource::collection($this->whenLoaded('items')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
