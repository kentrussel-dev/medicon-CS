<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'record_date',
        'diagnosis',
        'clinical_notes',
        'treatment_plan',
        'vital_signs',
        'icd_10_codes',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'diagnosis' => 'encrypted',
            'clinical_notes' => 'encrypted',
            'treatment_plan' => 'encrypted',
            'vital_signs' => 'encrypted:array',
            'icd_10_codes' => 'array',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'record_date' => $this->record_date?->toDateString(),
            'diagnosis' => $this->diagnosis,
            'clinical_notes' => $this->clinical_notes,
            'treatment_plan' => $this->treatment_plan,
            'icd_10_codes' => is_array($this->icd_10_codes) ? implode(' ', $this->icd_10_codes) : $this->icd_10_codes,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
