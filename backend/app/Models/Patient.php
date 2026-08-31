<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'blood_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'medical_notes',
        'insurance_provider',
        'insurance_policy_number',
        'scholarship',
        'hypertension',
        'diabetes',
        'alcoholism',
        'handicap_level',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'allergies' => 'encrypted',
            'medical_notes' => 'encrypted',
            'scholarship' => 'boolean',
            'hypertension' => 'boolean',
            'diabetes' => 'boolean',
            'alcoholism' => 'boolean',
            'handicap_level' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function calculateAge(): int
    {
        return $this->date_of_birth ? (int) $this->date_of_birth->diffInYears(now()) : 35;
    }
}
