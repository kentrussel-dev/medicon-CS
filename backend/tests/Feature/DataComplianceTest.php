<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DataComplianceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_patient_can_export_full_health_record_data_in_structured_json(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@medicon.health',
            'role' => UserRole::PATIENT,
        ]);
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $doctor = Doctor::factory()->create();

        // 1. Appointment
        $appt = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => 'paid',
        ]);

        // 2. Medical Record
        MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appt->id,
            'diagnosis' => 'Essential Hypertension',
        ]);

        // 3. Prescription
        Prescription::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appt->id,
            'notes' => 'Take Lisinopril daily',
        ]);

        // 4. Payment
        Payment::create([
            'appointment_id' => $appt->id,
            'user_id' => $user->id,
            'amount_cents' => 12000,
            'currency' => 'PHP',
            'gateway' => 'paymongo',
            'payment_method' => 'gcash',
            'status' => 'paid',
            'gateway_payment_id' => 'pay_pm_export_123',
        ]);

        // 5. Audit Log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => AuditAction::VIEW,
            'entity_type' => 'MedicalRecord',
            'entity_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Medicon Test',
        ]);

        $response = $this->actingAs($user)->getJson('/api/compliance/export');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'filename',
                'data' => [
                    'compliance_standard',
                    'export_generated_at',
                    'patient_profile' => ['user_id', 'name', 'email', 'blood_type', 'allergies'],
                    'appointments',
                    'medical_records',
                    'prescriptions',
                    'payments',
                    'audit_logs',
                ],
            ]);

        $this->assertCount(1, $response->json('data.appointments'));
        $this->assertCount(1, $response->json('data.medical_records'));
        $this->assertCount(1, $response->json('data.prescriptions'));
        $this->assertCount(1, $response->json('data.payments'));
        $this->assertCount(1, $response->json('data.audit_logs'));
    }

    public function test_patient_can_request_account_deletion_and_anonymize_personal_pii(): void
    {
        $user = User::factory()->create([
            'name' => 'John Identifying Name',
            'email' => 'john.sensitive@medicon.health',
            'phone' => '+15551234567',
            'password' => Hash::make('CorrectPassword123!'),
            'role' => UserRole::PATIENT,
        ]);
        $patient = Patient::factory()->create([
            'user_id' => $user->id,
            'emergency_contact_phone' => '+15559998888',
        ]);

        $response = $this->actingAs($user)->postJson('/api/compliance/account-deletion', [
            'password' => 'CorrectPassword123!',
            'reason' => 'Relocating to another country',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // User record is soft deleted and credentials anonymized
        $deletedUser = User::withTrashed()->find($user->id);
        $this->assertTrue($deletedUser->trashed());
        $this->assertStringStartsWith('Anonymized Patient #', $deletedUser->name);
        $this->assertStringContainsString('@deleted.medicon.local', $deletedUser->email);
        $this->assertNull($deletedUser->phone);
        $this->assertFalse($deletedUser->is_active);

        // Patient demographics sanitized
        $deletedPatient = Patient::withTrashed()->find($patient->id);
        $this->assertTrue($deletedPatient->trashed());
        $this->assertNull($deletedPatient->emergency_contact_phone);
    }

    public function test_account_deletion_preserves_immutable_hipaa_audit_trail(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123!'),
            'role' => UserRole::PATIENT,
        ]);
        Patient::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson('/api/compliance/account-deletion', [
            'password' => 'Password123!',
        ]);

        // An immutable deletion audit log record must exist with retention policy attached
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditAction::DELETE->value,
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);

        $log = AuditLog::where('user_id', $user->id)
            ->where('action', AuditAction::DELETE->value)
            ->first();

        $this->assertEquals('HIPAA 7-Year Forensic Retention Mandate', $log->new_values['retention_policy']);
    }
}
