<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class SetAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isDoctor() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'slots.*.start_time' => ['required', 'date_format:H:i', 'before:slots.*.end_time'],
            'slots.*.end_time' => ['required', 'date_format:H:i'],
            'slots.*.slot_duration_minutes' => ['sometimes', 'integer', 'in:15,20,30,45,60'],
            'slots.*.is_active' => ['sometimes', 'boolean'],
        ];
    }
}
