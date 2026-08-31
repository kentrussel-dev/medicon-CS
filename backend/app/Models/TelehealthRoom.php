<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TelehealthRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_code',
        'appointment_id',
        'created_by',
        'title',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    /**
     * Generate a unique room code formatted like `sdf-sdyy-125` or `med-7x3q-992`.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $part1 = Str::lower(Str::random(3));
            $part2 = Str::lower(Str::random(4));
            $part3 = (string) rand(100, 999);
            $code = "{$part1}-{$part2}-{$part3}";
        } while (static::where('room_code', $code)->exists());

        return $code;
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TelehealthMessage::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Close the consultation room and purge all ephemeral in-call data.
     */
    public function closeAndPurge(): void
    {
        $this->update([
            'status' => 'CLOSED',
            'closed_at' => now(),
        ]);

        if ($this->appointment_id) {
            // Securely purge all in-room messages for HIPAA privacy & data hygiene
            TelehealthMessage::where('appointment_id', $this->appointment_id)->delete();
        }
    }
}
