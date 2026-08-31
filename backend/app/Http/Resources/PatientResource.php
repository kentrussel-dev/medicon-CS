<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'age' => $this->calculateAge(),
            'gender' => $this->gender,
            'blood_type' => $this->blood_type,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'allergies' => $this->allergies,
            'medical_notes' => $this->medical_notes,
            'insurance_provider' => $this->insurance_provider,
            'insurance_policy_number' => $this->insurance_policy_number,
            'scholarship' => $this->scholarship,
            'hypertension' => $this->hypertension,
            'diabetes' => $this->diabetes,
            'alcoholism' => $this->alcoholism,
            'handicap_level' => $this->handicap_level,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
