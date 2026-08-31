<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'scheduled_start',
        'scheduled_end',
        'status',
        'type',
        'reason',
        'notes',
        'no_show_risk_score',
        'no_show_risk_level',
        'risk_factors',
        'is_reminder_sent',
        'cancellation_reason',
        'meeting_link',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'status' => AppointmentStatus::class,
            'type' => AppointmentType::class,
            'no_show_risk_level' => RiskLevel::class,
            'no_show_risk_score' => 'float',
            'notes' => 'encrypted',
            'risk_factors' => 'array',
            'is_reminder_sent' => 'boolean',
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

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentParticipant::class);
    }

    public function isPast(): bool
    {
        return $this->scheduled_start->isPast();
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED], true)
            && $this->scheduled_start->isFuture();
    }
}
