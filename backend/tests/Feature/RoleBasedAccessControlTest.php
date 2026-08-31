<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/medical-records');
        $response->assertStatus(401);
    }

    public function test_patient_cannot_view_other_patients_medical_record(): void
    {
        $patient1User = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient1 = Patient::factory()->create(['user_id' => $patient1User->id]);

        $patient2User = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient2 = Patient::factory()->create(['user_id' => $patient2User->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $record = MedicalRecord::create([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'record_date' => now()->toDateString(),
            'diagnosis' => 'Confidential health diagnosis',
            'clinical_notes' => 'Clinical notes for patient 2 only.',
        ]);

        // Patient 1 tries to read Patient 2's record
        Sanctum::actingAs($patient1User);
        $response = $this->getJson("/api/medical-records/{$record->id}");

        $response->assertStatus(403);
    }

    public function test_patient_can_view_their_own_medical_record(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $record = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'record_date' => now()->toDateString(),
            'diagnosis' => 'Seasonal allergic rhinitis',
            'clinical_notes' => 'Patient has clear nasal discharge.',
        ]);

        Sanctum::actingAs($patientUser);
        $response = $this->getJson("/api/medical-records/{$record->id}");

        $response->assertStatus(200)
            ->assertJsonPath('record.diagnosis', 'Seasonal allergic rhinitis');
    }

    public function test_only_admin_can_access_audit_logs(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        Sanctum::actingAs($patientUser);

        $response = $this->getJson('/api/admin/audit-logs');
        $response->assertStatus(403);

        $adminUser = User::factory()->admin()->create();
        Sanctum::actingAs($adminUser);

        $adminResponse = $this->getJson('/api/admin/audit-logs');
        $adminResponse->assertStatus(200);
    }
}
