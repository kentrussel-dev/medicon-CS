<?php

namespace App\Http\Requests\Appointment;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(AppointmentStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'cancellation_reason' => ['required_if:status,CANCELLED', 'nullable', 'string', 'max:500'],
        ];
    }
}
