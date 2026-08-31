<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isDoctor() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'diagnosis' => ['required', 'string', 'min:3', 'max:5000'],
            'clinical_notes' => ['required', 'string', 'min:5', 'max:15000'],
            'treatment_plan' => ['nullable', 'string', 'max:10000'],
            'vital_signs' => ['nullable', 'array'],
            'vital_signs.blood_pressure' => ['nullable', 'string', 'max:20'],
            'vital_signs.heart_rate' => ['nullable', 'numeric', 'min:30', 'max:250'],
            'vital_signs.temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'vital_signs.oxygen_saturation' => ['nullable', 'numeric', 'min:50', 'max:100'],
            'vital_signs.weight_kg' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'icd_10_codes' => ['nullable', 'array'],
            'icd_10_codes.*' => ['string', 'max:20'],
        ];
    }
}
