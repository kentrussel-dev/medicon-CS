<?php

namespace App\Http\Requests\Prescription;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
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
            'valid_until' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medication_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['required', 'string', 'max:100'],
            'items.*.frequency' => ['required', 'string', 'max:100'],
            'items.*.duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'items.*.instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
