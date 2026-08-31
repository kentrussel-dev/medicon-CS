<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'amount_cents',
        'currency',
        'gateway',
        'payment_method',
        'status',
        'gateway_payment_id',
        'gateway_client_secret',
        'refund_amount_cents',
        'refunded_at',
        'cancellation_fee_cents',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'refund_amount_cents' => 'integer',
            'cancellation_fee_cents' => 'integer',
            'refunded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatted amount in Philippine Pesos (e.g. ₱120.00)
     */
    public function getAmountPesosAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2);
    }

    /**
     * Formatted refund amount in Philippine Pesos
     */
    public function getRefundAmountPesosAttribute(): string
    {
        return number_format($this->refund_amount_cents / 100, 2);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
