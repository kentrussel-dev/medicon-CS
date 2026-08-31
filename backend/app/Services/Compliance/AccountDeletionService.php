<?php

namespace App\Services\Compliance;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountDeletionService
{
    /**
     * Anonymize and delete user account to satisfy Right to be Forgotten
     * while preserving immutable clinical audit trails required by law.
     */
    public function deleteAndAnonymizeAccount(User $user, ?string $reason = 'user_requested_deletion'): array
    {
        return DB::transaction(function () use ($user, $reason) {
            $userId = $user->id;
            $originalRole = $user->role?->value ?? (string) $user->role;

            // 1. Immutable Audit Log Entry of deletion event
            AuditLog::create([
                'user_id' => $userId,
                'action' => AuditAction::DELETE,
                'entity_type' => 'User',
                'entity_id' => $userId,
                'old_values' => ['anonymized' => false, 'status' => 'active'],
                'new_values' => [
                    'anonymized' => true,
                    'status' => 'deleted',
                    'reason' => $reason,
                    'retention_policy' => 'HIPAA 7-Year Forensic Retention Mandate',
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System Compliance Service',
            ]);

            // 2. Anonymize Patient Demographics (if applicable)
            if ($patient = $user->patient) {
                $patient->update([
                    'emergency_contact_name' => null,
                    'emergency_contact_phone' => null,
                    'allergies' => '[ANONYMIZED]',
                ]);
                $patient->delete();
            }

            // 3. Anonymize User Credentials & Identifying PII
            $user->tokens()->delete(); // Revoke all active API tokens

            $user->forceFill([
                'name' => "Anonymized Patient #{$userId}",
                'email' => "anonymized-{$userId}-" . Str::random(6) . "@deleted.medicon.local",
                'password' => Hash::make(Str::random(32)),
                'phone' => null,
                'avatar_url' => null,
                'is_active' => false,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ])->save();

            // 4. Soft Delete User
            $user->delete();

            return [
                'success' => true,
                'message' => 'Your account has been permanently anonymized and closed. In compliance with HIPAA, non-identifying clinical audit records are securely retained.',
                'user_id' => $userId,
                'anonymized_at' => now()->toIso8601String(),
            ];
        });
    }
}
