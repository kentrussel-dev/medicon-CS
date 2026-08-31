<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $patientJane = Patient::first();
        $patientJohn = Patient::skip(1)->first() ?? $patientJane;
        $patientEmily = Patient::skip(2)->first() ?? $patientJane;

        $docSarah = Doctor::where('specialty', 'Cardiology')->first() ?? Doctor::first();
        $docMarcus = Doctor::where('specialty', 'Neurology')->first() ?? Doctor::skip(1)->first();
        $docWilson = Doctor::where('specialty', 'General Practice')->first() ?? Doctor::skip(3)->first();

        if (!$patientJane || !$docSarah) {
            return;
        }

        // 1. Comprehensive Cardiology Encounter Record (Jane Doe)
        $rec1 = MedicalRecord::create([
            'patient_id' => $patientJane->id,
            'doctor_id' => $docSarah->id,
            'appointment_id' => 5,
            'visit_date' => Carbon::now()->subDays(21),
            'chief_complaint' => 'Quarterly cardiovascular evaluation and episodic borderline systolic hypertension readings at home.',
            'diagnosis' => 'Essential Primary Hypertension (ICD-10: I10), Mild Hyperlipidemia (ICD-10: E78.5)',
            'treatment_plan' => 'Initiated low-dose ACE-inhibitor (Lisinopril 10mg) and HMG-CoA reductase inhibitor (Atorvastatin 20mg). Recommended DASH dietary pattern (<2,000mg Na/day) and daily telemetry blood pressure logging.',
            'subjective' => 'Patient reports occasional tension headaches in late afternoons. Denies chest pain, orthopnea, palpitations, or lower extremity edema. Compliance with aerobic walking routine is 4x weekly.',
            'objective' => "BP: 138/86 mmHg | HR: 72 bpm regular | SpO2: 99% on room air | BMI: 23.8 kg/m2\nCardiovascular: Normal S1/S2, no murmurs, rubs, or gallops. Peripheral pulses +2 bilaterally.\nLungs: Clear to auscultation bilaterally. No wheezing or crackles.",
            'assessment' => 'Stage 1 Essential Hypertension with excellent functional status. Cardiovascular risk score low-to-moderate.',
            'plan' => '1. Lisinopril 10mg PO daily in morning.\n2. Atorvastatin 20mg PO daily at bedtime.\n3. Basic Metabolic Panel (BMP) & Lipid Profile in 8 weeks.\n4. Follow-up via Telehealth in 3 months.',
            'is_confidential' => false,
        ]);

        // Prescription for Jane Doe
        $rx1 = Prescription::create([
            'patient_id' => $patientJane->id,
            'doctor_id' => $docSarah->id,
            'appointment_id' => 5,
            'notes' => 'Take with a full glass of water. Monitor blood pressure weekly.',
            'is_dispensed' => false,
            'valid_until' => Carbon::now()->addMonths(6),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx1->id,
            'medication_name' => 'Lisinopril',
            'dosage' => '10mg',
            'frequency' => 'Once daily in the morning',
            'duration_days' => 90,
            'instructions' => 'Take 1 tablet every morning with or without food. Avoid potassium supplements unless directed.',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx1->id,
            'medication_name' => 'Atorvastatin Calcium',
            'dosage' => '20mg',
            'frequency' => 'Once daily at bedtime',
            'duration_days' => 90,
            'instructions' => 'Take 1 tablet orally at bedtime. Report any unexplained muscle soreness.',
        ]);

        // 2. Neurology Headache Record (Emily Clark)
        $rec2 = MedicalRecord::create([
            'patient_id' => $patientEmily->id,
            'doctor_id' => $docMarcus->id,
            'visit_date' => Carbon::now()->subDays(30),
            'chief_complaint' => 'Unilateral pulsating frontal headaches with photophobia and scintillating scotoma aura.',
            'diagnosis' => 'Migraine with Aura, not intractable (ICD-10: G43.109)',
            'treatment_plan' => 'Prescribed Sumatriptan 50mg for acute abortive therapy. Maintained headache diary to identify circadian triggers.',
            'subjective' => 'Patient experiences 3-4 episodes per month, typically precipitated by fluorescent light exposure and sleep disruptions. Relief noted with dark quiet room.',
            'objective' => "BP: 118/76 mmHg | HR: 68 bpm | Neurological Exam: Cranial nerves II-XII grossly intact. No focal deficits, sensory loss, or cerebellar ataxia.",
            'assessment' => 'Classical migraine with typical visual aura. Normal neurovascular baseline.',
            'plan' => 'Sumatriptan 50mg PO at onset of aura. Max 100mg/24hr. Follow-up headache diary in 60 days.',
            'is_confidential' => false,
        ]);

        $rx2 = Prescription::create([
            'patient_id' => $patientEmily->id,
            'doctor_id' => $docMarcus->id,
            'notes' => 'Acute abortive migraine medication.',
            'is_dispensed' => true,
            'valid_until' => Carbon::now()->addMonths(6),
        ]);

        PrescriptionItem::create([
            'prescription_id' => $rx2->id,
            'medication_name' => 'Sumatriptan Succinate',
            'dosage' => '50mg',
            'frequency' => 'As needed for acute migraine onset',
            'duration_days' => 30,
            'instructions' => 'Take 1 tablet immediately at onset of headache or aura. May repeat in 2 hours if headache persists (max 100mg/day).',
        ]);

        // 3. Metabolic Wellness Encounter (John Miller)
        MedicalRecord::create([
            'patient_id' => $patientJohn->id,
            'doctor_id' => $docWilson->id,
            'visit_date' => Carbon::now()->subDays(45),
            'chief_complaint' => 'Routine biannual Type 2 diabetes metabolic evaluation.',
            'diagnosis' => 'Type 2 Diabetes Mellitus without complications (ICD-10: E11.9)',
            'treatment_plan' => 'Continue Metformin 500mg BID. Fasting glucose well-controlled (108 mg/dL). HbA1c 6.4%.',
            'subjective' => 'Adherent to dietary guidelines. Denies polyuria, polydipsia, or peripheral tingling.',
            'objective' => "BP: 126/82 mmHg | HR: 70 bpm | Weight: 84.5 kg | HbA1c: 6.4% | Fasting Glucose: 108 mg/dL | Monofilament foot exam: Normal sensation bilaterally.",
            'assessment' => 'Well-controlled Type 2 Diabetes Mellitus under monotherapy.',
            'plan' => 'Maintain Metformin 500mg BID with morning and evening meals. Annual dilated eye exam scheduled.',
            'is_confidential' => false,
        ]);
    }
}
