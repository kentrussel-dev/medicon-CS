<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLoggerService
{
    public function __construct(protected ?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public function log(
        AuditAction $action,
        string $recordType,
        ?int $recordId = null,
        ?int $patientId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null
    ): AuditLog {
        $actor = $actor ?? $this->request?->user();
        $ip = $this->request?->ip() ?? '127.0.0.1';
        $userAgent = $this->request?->userAgent() ?? 'System / Internal';

        // Strip any password or raw binary keys
        $sanitizedOld = $this->sanitizeDiff($oldValues);
        $sanitizedNew = $this->sanitizeDiff($newValues);

        $log = AuditLog::create([
            'user_id' => $actor?->id,
            'patient_id' => $patientId,
            'action' => $action,
            'record_type' => class_basename($recordType),
            'record_id' => $recordId,
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 500),
            'old_values' => $sanitizedOld,
            'new_values' => $sanitizedNew,
            'created_at' => now(),
        ]);

        // Write structured audit log to secure audit channel
        Log::channel('audit')->info('AUDIT_EVENT', [
            'audit_id' => $log->id,
            'user_id' => $actor?->id,
            'user_email' => $actor?->email,
            'user_role' => $actor?->role?->value,
            'patient_id' => $patientId,
            'action' => $action->value,
            'record_type' => class_basename($recordType),
            'record_id' => $recordId,
            'ip_address' => $ip,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $log;
    }

    public function logView(Model $record, ?int $patientId = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            action: AuditAction::VIEW,
            recordType: get_class($record),
            recordId: $record->getKey(),
            patientId: $patientId ?? ($record->patient_id ?? null),
            actor: $actor
        );
    }

    public function logCreate(Model $record, ?int $patientId = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            action: AuditAction::CREATE,
            recordType: get_class($record),
            recordId: $record->getKey(),
            patientId: $patientId ?? ($record->patient_id ?? null),
            newValues: $record->getAttributes(),
            actor: $actor
        );
    }

    public function logUpdate(Model $record, array $original, ?int $patientId = null, ?User $actor = null): AuditLog
    {
        $dirty = $record->getDirty();
        $old = array_intersect_key($original, $dirty);

        return $this->log(
            action: AuditAction::UPDATE,
            recordType: get_class($record),
            recordId: $record->getKey(),
            patientId: $patientId ?? ($record->patient_id ?? null),
            oldValues: $old,
            newValues: $dirty,
            actor: $actor
        );
    }

    public function logDelete(Model $record, ?int $patientId = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            action: AuditAction::DELETE,
            recordType: get_class($record),
            recordId: $record->getKey(),
            patientId: $patientId ?? ($record->patient_id ?? null),
            oldValues: $record->getAttributes(),
            actor: $actor
        );
    }

    protected function sanitizeDiff(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $redactedKeys = ['password', 'remember_token', 'token'];
        foreach ($redactedKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }
        return $data;
    }
}
