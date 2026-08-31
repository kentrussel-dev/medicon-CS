<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'scheduled_start' => ['required', 'date', 'after:now'],
            'scheduled_end' => ['required', 'date', 'after:scheduled_start'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
