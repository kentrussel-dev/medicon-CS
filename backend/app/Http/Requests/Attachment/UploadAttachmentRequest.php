<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB in KB
                'mimes:pdf,jpg,jpeg,png,dcm',
            ],
            'attachable_type' => ['required', 'string', 'in:MedicalRecord,Appointment,Patient'],
            'attachable_id' => ['required', 'integer'],
        ];
    }
}
