<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncryptedMedicalRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_medical_fields_are_encrypted_in_database(): void
    {
        $patientUser = User::factory()->create();
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);
        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $plainDiagnosis = 'Severe acute migraine with sensory aura';
        $plainClinicalNotes = 'Administered subcutaneous sumatriptan 6mg. Vital signs stabilized.';
        $plainTreatmentPlan = 'Maintain symptom diary and follow up in two weeks.';

        $record = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'record_date' => now()->toDateString(),
            'diagnosis' => $plainDiagnosis,
            'clinical_notes' => $plainClinicalNotes,
            'treatment_plan' => $plainTreatmentPlan,
            'vital_signs' => [
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
            ],
        ]);

        // Query raw database row directly without Eloquent model casting
        $rawRow = DB::table('medical_records')->where('id', $record->id)->first();

        // Ensure raw database values do NOT contain plain text sensitive strings
        $this->assertNotEquals($plainDiagnosis, $rawRow->diagnosis);
        $this->assertNotEquals($plainClinicalNotes, $rawRow->clinical_notes);
        $this->assertNotEquals($plainTreatmentPlan, $rawRow->treatment_plan);

        // Ensure Eloquent model automatically decrypts transparently
        $freshRecord = MedicalRecord::find($record->id);
        $this->assertEquals($plainDiagnosis, $freshRecord->diagnosis);
        $this->assertEquals($plainClinicalNotes, $freshRecord->clinical_notes);
        $this->assertEquals($plainTreatmentPlan, $freshRecord->treatment_plan);
        $this->assertEquals('120/80', $freshRecord->vital_signs['blood_pressure']);
    }
}
