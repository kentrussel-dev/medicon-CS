<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\RiskLevel;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $start = Carbon::now()->addDays(fake()->numberBetween(1, 20))->setHour(fake()->numberBetween(9, 16))->setMinute(0)->setSecond(0);
        $end = $start->copy()->addMinutes(30);

        $riskScore = fake()->randomFloat(4, 0.05, 0.85);
        $riskLevel = $riskScore >= 0.65 ? RiskLevel::HIGH : ($riskScore >= 0.35 ? RiskLevel::MEDIUM : RiskLevel::LOW);

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'scheduled_start' => $start,
            'scheduled_end' => $end,
            'status' => AppointmentStatus::CONFIRMED,
            'type' => fake()->randomElement([AppointmentType::TELEHEALTH, AppointmentType::IN_PERSON]),
            'reason' => fake()->randomElement([
                'Routine annual wellness exam and bloodwork consultation',
                'Persistent migraine symptoms and light sensitivity',
                'Follow-up review for blood pressure medication titration',
                'Skin rash evaluation and allergy patch testing',
                'Anxiety and insomnia consultation',
            ]),
            'notes' => 'Patient requested video link and preliminary medication review.',
            'no_show_risk_score' => $riskScore,
            'no_show_risk_level' => $riskLevel,
            'risk_factors' => ['Booking lead time: ' . fake()->numberBetween(3, 14) . ' days'],
            'is_reminder_sent' => fake()->boolean(60),
            'meeting_link' => 'https://meet.medicon.health/room/' . fake()->uuid(),
        ];
    }
}
