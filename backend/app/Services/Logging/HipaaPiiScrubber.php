<?php

namespace App\Services\Logging;

use Illuminate\Support\Str;

class HipaaPiiScrubber
{
    /**
     * Keys containing sensitive patient clinical data or credentials to redact
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'token',
        'auth_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'secret',
        'credit_card',
        'card_number',
        'cvv',
        'card_cvc',
        'clinical_notes',
        'diagnosis',
        'vital_signs',
        'treatment_plan',
        'allergies',
        'blood_type',
        'emergency_contact_phone',
        'emergency_contact_name',
        'notes',
        'risk_factors',
        'encrypted_data',
        'date_of_birth',
        'dob',
        'phone',
    ];

    /**
     * Process and sanitize event payload before dispatching to Sentry
     */
    public static function processEvent(array $event, ?\Illuminate\Http\Request $request = null): array
    {
        // 1. Context Enrichment (Non-sensitive operational telemetry)
        if ($request && $user = $request->user()) {
            $event['user'] = [
                'id' => $user->id,
                'role' => $user->role?->value ?? (string) $user->role,
                'is_active' => $user->is_active,
            ];

            $event['tags'] = array_merge($event['tags'] ?? [], [
                'user_role' => $user->role?->value ?? (string) $user->role,
                'request_id' => $request->header('X-Request-ID', (string) Str::uuid()),
                'endpoint' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        // 2. Deep PII / HIPAA Data Scrubbing
        if (isset($event['request']['data'])) {
            $event['request']['data'] = self::scrubData($event['request']['data']);
        }

        if (isset($event['extra'])) {
            $event['extra'] = self::scrubData($event['extra']);
        }

        // 3. Breadcrumbs scrubbing
        if (isset($event['breadcrumbs']['values']) && is_array($event['breadcrumbs']['values'])) {
            foreach ($event['breadcrumbs']['values'] as &$crumb) {
                if (isset($crumb['data']) && is_array($crumb['data'])) {
                    $crumb['data'] = self::scrubData($crumb['data']);
                }
            }
        }

        return $event;
    }

    /**
     * Recursively redact sensitive keys from array or JSON structures
     */
    public static function scrubData(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, self::$sensitiveKeys, true) || Str::contains($lowerKey, ['secret', 'token', 'pass', 'card', 'clinical', 'diagnos', 'vital'])) {
                $data[$key] = '[REDACTED_HIPAA_PII]';
            } elseif (is_array($value)) {
                $data[$key] = self::scrubData($value);
            }
        }

        return $data;
    }
}
