<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_id' => null,
            'record_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'diagnosis' => fake()->randomElement([
                'Essential (primary) hypertension, benign stage 1',
                'Acute tension-type headache with cervical muscle spasm',
                'Type 2 diabetes mellitus without complications',
                'Generalized anxiety disorder with somatic manifestations',
                'Contact dermatitis, unspecified etiology',
            ]),
            'clinical_notes' => 'Patient presents for scheduled consultation. Physical examination reveals stable vital parameters. Heart sounds S1/S2 regular, lungs clear to auscultation bilaterally. Discussed lifestyle modifications, sodium intake reduction, and aerobic exercise 150 min/week.',
            'treatment_plan' => 'Prescribed first-line antihypertensive therapy. Ordered repeat lipid panel and comprehensive metabolic panel in 90 days. Return to clinic if blood pressure exceeds 140/90 mmHg.',
            'vital_signs' => [
                'blood_pressure' => fake()->randomElement(['120/80', '128/84', '135/88', '118/76']),
                'heart_rate' => fake()->numberBetween(64, 88),
                'temperature_c' => fake()->randomFloat(1, 36.5, 37.2),
                'oxygen_saturation' => fake()->numberBetween(97, 100),
                'weight_kg' => fake()->randomFloat(1, 62.0, 88.5),
            ],
            'icd_10_codes' => fake()->randomElement([
                ['I10', 'Z71.3'],
                ['G44.209'],
                ['E11.9', 'Z79.4'],
                ['F41.1'],
                ['L25.9'],
            ]),
        ];
    }
}
