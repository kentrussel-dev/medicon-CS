<?php

namespace App\Http\Requests\Appointment;

use App\Enums\AppointmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'scheduled_start' => ['required', 'date', 'after:now'],
            'scheduled_end' => ['required', 'date', 'after:scheduled_start'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
            'type' => ['sometimes', new Enum(AppointmentType::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'patient_id' => ['sometimes', 'nullable', 'integer', 'exists:patients,id'], // Admin proxy booking
        ];
    }
}
