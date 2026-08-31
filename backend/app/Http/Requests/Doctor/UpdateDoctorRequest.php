<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isDoctor() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'specialty' => ['sometimes', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'consultation_fee' => ['sometimes', 'numeric', 'min:0', 'max:5000'],
            'years_of_experience' => ['sometimes', 'integer', 'min:0', 'max:70'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
