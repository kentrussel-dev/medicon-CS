<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::where('status', \App\Enums\AppointmentStatus::COMPLETED)->get();

        foreach ($appointments as $appointment) {
            $record = MedicalRecord::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'record_date' => $appointment->scheduled_start->toDateString(),
                'diagnosis' => 'Essential (primary) hypertension, stage 1 without end-organ compromise',
                'clinical_notes' => 'Patient attended telehealth encounter. Vitals reviewed. Cardiovascular exam unremarkable on remote telemetry. Patient advised on dietary sodium reduction < 2g/day and daily blood pressure journaling.',
                'treatment_plan' => 'Initiate Lisinopril 10mg once daily. Order renal function panel in 6 weeks. Follow up in clinic in 3 months.',
                'vital_signs' => [
                    'blood_pressure' => '134/86',
                    'heart_rate' => 74,
                    'temperature_c' => 36.8,
                    'oxygen_saturation' => 99,
                    'weight_kg' => 78.5,
                ],
                'icd_10_codes' => ['I10', 'Z71.3'],
            ]);

            $prescription = Prescription::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'notes' => 'Take in the morning with a full glass of water. Report any dry cough.',
                'is_dispensed' => true,
                'valid_until' => Carbon::now()->addMonths(6)->toDateString(),
            ]);

            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medication_name' => 'Lisinopril Tablets USP',
                'dosage' => '10 mg',
                'frequency' => 'Once daily in the morning',
                'duration_days' => 90,
                'instructions' => 'Take with or without food. Avoid potassium supplements.',
            ]);

            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medication_name' => 'Hydrochlorothiazide',
                'dosage' => '12.5 mg',
                'frequency' => 'Once daily in the morning',
                'duration_days' => 90,
                'instructions' => 'Take in morning to prevent nocturia.',
            ]);
        }
    }
}
