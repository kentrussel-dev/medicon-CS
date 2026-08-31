<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            
            // Patient specific fields
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:M,F,Other'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'insurance_provider' => ['nullable', 'string', 'max:100'],
            'insurance_policy_number' => ['nullable', 'string', 'max:100'],
            'scholarship' => ['nullable', 'boolean'],
            'hypertension' => ['nullable', 'boolean'],
            'diabetes' => ['nullable', 'boolean'],
            'alcoholism' => ['nullable', 'boolean'],
            'handicap_level' => ['nullable', 'integer', 'min:0', 'max:4'],

            // Doctor specific fields
            'specialty' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
        ];
    }
}
