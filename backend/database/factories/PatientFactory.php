<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['M', 'F']),
            'blood_type' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+']),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
            'allergies' => fake()->randomElement(['Penicillin, Peanuts', 'Latex, Sulfa Drugs', 'None Known', 'Aspirin']),
            'medical_notes' => 'Patient has mild seasonal allergies and no chronic contraindications.',
            'insurance_provider' => fake()->randomElement(['BlueCross', 'Aetna', 'UnitedHealth', 'Kaiser']),
            'insurance_policy_number' => 'POL-' . fake()->numerify('######'),
            'scholarship' => fake()->boolean(15),
            'hypertension' => fake()->boolean(25),
            'diabetes' => fake()->boolean(12),
            'alcoholism' => fake()->boolean(4),
            'handicap_level' => fake()->randomElement([0, 0, 0, 1]),
        ];
    }
}
