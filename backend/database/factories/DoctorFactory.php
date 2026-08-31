<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->doctor(),
            'specialty' => fake()->randomElement([
                'Cardiology',
                'Dermatology',
                'Neurology',
                'Pediatrics',
                'Psychiatry',
                'General Practice',
                'Endocrinology',
            ]),
            'license_number' => 'MD-' . fake()->unique()->numerify('#####'),
            'bio' => 'Board-certified medical practitioner committed to clinical excellence, evidence-based care, and empathetic patient communication.',
            'consultation_fee' => fake()->randomElement([60.00, 75.00, 90.00, 120.00, 150.00]),
            'years_of_experience' => fake()->numberBetween(4, 25),
            'rating' => fake()->randomFloat(2, 4.70, 5.00),
            'is_active' => true,
        ];
    }
}
