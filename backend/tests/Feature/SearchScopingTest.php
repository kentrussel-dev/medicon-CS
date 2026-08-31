<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SearchScopingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_doctor_search_by_name_and_specialty(): void
    {
        $docUser1 = User::factory()->create(['name' => 'Dr. Gregory House', 'role' => UserRole::DOCTOR]);
        Doctor::factory()->create([
            'user_id' => $docUser1->id,
            'specialty' => 'Infectious Disease',
            'is_active' => true,
        ]);

        $docUser2 = User::factory()->create(['name' => 'Dr. Meredith Grey', 'role' => UserRole::DOCTOR]);
        Doctor::factory()->create([
            'user_id' => $docUser2->id,
            'specialty' => 'General Surgery',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/search?q=Infectious&type=doctors');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.doctors')
            ->assertJsonPath('data.doctors.0.name', 'Dr. Gregory House');
    }

    public function test_patient_can_only_search_own_medical_records_never_another_patient(): void
    {
        $patientAUser = User::factory()->create(['name' => 'Patient Alice', 'role' => UserRole::PATIENT]);
        $patientA = Patient::factory()->create(['user_id' => $patientAUser->id]);

        $patientBUser = User::factory()->create(['name' => 'Patient Bob', 'role' => UserRole::PATIENT]);
        $patientB = Patient::factory()->create(['user_id' => $patientBUser->id]);

        $doctor = Doctor::factory()->create();

        // Alice's Record
        MedicalRecord::factory()->create([
            'patient_id' => $patientA->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Alice Hypertension Screening Result',
            'clinical_notes' => 'Alice blood pressure readings are stable.',
        ]);

        // Bob's Record (contains sensitive search keyword)
        MedicalRecord::factory()->create([
            'patient_id' => $patientB->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Bob Acute Hypertension Crisis',
            'clinical_notes' => 'Bob admitted for elevated blood pressure.',
        ]);

        // When Alice searches "Hypertension", Alice must ONLY see her own record, never Bob's
        $responseAlice = $this->actingAs($patientAUser)->getJson('/api/search?q=Hypertension&type=records');

        $responseAlice->assertStatus(200)
            ->assertJsonCount(1, 'data.records')
            ->assertJsonPath('data.records.0.diagnosis', 'Alice Hypertension Screening Result');
    }

    public function test_patient_can_only_search_own_prescriptions_never_another_patient(): void
    {
        $patientAUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patientA = Patient::factory()->create(['user_id' => $patientAUser->id]);

        $patientBUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patientB = Patient::factory()->create(['user_id' => $patientBUser->id]);

        $doctor = Doctor::factory()->create();

        // Alice's Prescription
        $rxA = Prescription::factory()->create([
            'patient_id' => $patientA->id,
            'doctor_id' => $doctor->id,
            'notes' => 'Alice daily Lisinopril medication order',
        ]);
        PrescriptionItem::create([
            'prescription_id' => $rxA->id,
            'medication_name' => 'Lisinopril',
            'dosage' => '10mg',
            'route' => 'Oral',
            'frequency' => 'Daily',
            'refills_remaining' => 2,
        ]);

        // Bob's Prescription
        $rxB = Prescription::factory()->create([
            'patient_id' => $patientB->id,
            'doctor_id' => $doctor->id,
            'notes' => 'Bob daily Lisinopril medication order',
        ]);
        PrescriptionItem::create([
            'prescription_id' => $rxB->id,
            'medication_name' => 'Lisinopril',
            'dosage' => '20mg',
            'route' => 'Oral',
            'frequency' => 'Daily',
            'refills_remaining' => 1,
        ]);

        // Alice searches "Lisinopril"
        $response = $this->actingAs($patientAUser)->getJson('/api/search?q=Lisinopril&type=prescriptions');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.prescriptions')
            ->assertJsonPath('data.prescriptions.0.id', $rxA->id);
    }
}
