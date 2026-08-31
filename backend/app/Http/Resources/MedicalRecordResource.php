<?php

namespace App\Http\Resources;

use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
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
            'doctor_specialty' => $this->doctor?->specialty,
            'record_date' => $this->record_date?->toDateString(),
            'diagnosis' => $this->diagnosis,
            'clinical_notes' => $this->clinical_notes,
            'treatment_plan' => $this->treatment_plan,
            'vital_signs' => $this->vital_signs ?? [],
            'icd_10_codes' => $this->icd_10_codes ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
