<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialty',
        'license_number',
        'bio',
        'consultation_fee',
        'consultation_fee_cents',
        'years_of_experience',
        'rating',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'consultation_fee_cents' => 'integer',
            'years_of_experience' => 'integer',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getConsultationFeePesosAttribute(): string
    {
        $cents = $this->consultation_fee_cents ?? ($this->consultation_fee ? (int)round($this->consultation_fee * 100) : 12000);
        return number_format($cents / 100, 2);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'specialty' => $this->specialty,
            'license_number' => $this->license_number,
            'bio' => $this->bio,
            'consultation_fee_cents' => $this->consultation_fee_cents,
            'years_of_experience' => $this->years_of_experience,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class);
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
}
