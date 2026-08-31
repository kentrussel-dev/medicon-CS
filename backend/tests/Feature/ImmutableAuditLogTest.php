<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ImmutableAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_records_creation_and_fields(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['user_id' => $user->id]);

        $log = AuditLog::create([
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'action' => AuditAction::VIEW,
            'record_type' => 'Patient',
            'record_id' => $patient->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Medicon-Test-Agent/1.0',
            'new_values' => ['status' => 'active'],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => AuditAction::VIEW->value,
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_audit_logs_prevent_modifications_at_model_layer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Audit logs are immutable and cannot be modified.');

        $log = AuditLog::create([
            'action' => AuditAction::CREATE,
            'record_type' => 'MedicalRecord',
            'record_id' => 1,
            'ip_address' => '127.0.0.1',
        ]);

        $log->update(['ip_address' => '10.0.0.1']);
    }

    public function test_audit_logs_prevent_deletions_at_model_layer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Audit logs are immutable and cannot be deleted.');

        $log = AuditLog::create([
            'action' => AuditAction::DELETE,
            'record_type' => 'MedicalRecord',
            'record_id' => 1,
            'ip_address' => '127.0.0.1',
        ]);

        $log->delete();
    }
}
