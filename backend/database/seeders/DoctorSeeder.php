<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctorsData = [
            [
                'name' => 'Dr. Sarah Jenkins',
                'email' => 'sarah.jenkins@medicon.health',
                'specialty' => 'Cardiology',
                'license_number' => 'MD-CAR-88210',
                'bio' => 'Harvard Medical School alumna specializing in preventative cardiology, cardiac arrhythmias, and remote cardiovascular monitoring.',
                'fee' => 120.00,
                'experience' => 12,
                'rating' => 4.95,
                'avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Marcus Chen',
                'email' => 'marcus.chen@medicon.health',
                'specialty' => 'Neurology',
                'license_number' => 'MD-NEU-41903',
                'bio' => 'Neurologist with focus on cognitive health, migraine prophylaxis, and peripheral neuropathy management.',
                'fee' => 110.00,
                'experience' => 9,
                'rating' => 4.88,
                'avatar' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Elena Rostova',
                'email' => 'elena.rostova@medicon.health',
                'specialty' => 'Dermatology',
                'license_number' => 'MD-DER-33918',
                'bio' => 'Clinical dermatologist dedicated to teledermatology, eczema management, and early skin cancer detection.',
                'fee' => 95.00,
                'experience' => 8,
                'rating' => 4.92,
                'avatar' => 'https://images.unsplash.com/photo-1594824813689-53b53c7c25a0?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. James Wilson',
                'email' => 'james.wilson@medicon.health',
                'specialty' => 'General Practice',
                'license_number' => 'MD-GEN-77401',
                'bio' => 'Primary care physician providing holistic healthcare, chronic disease prevention, and telehealth wellness consultations.',
                'fee' => 65.00,
                'experience' => 15,
                'rating' => 4.90,
                'avatar' => 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Aisha Patel',
                'email' => 'aisha.patel@medicon.health',
                'specialty' => 'Psychiatry',
                'license_number' => 'MD-PSY-92014',
                'bio' => 'Adult psychiatrist providing compassionate behavioral health, anxiety care, and psychopharmacology consultations.',
                'fee' => 135.00,
                'experience' => 11,
                'rating' => 4.97,
                'avatar' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=150&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($doctorsData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Secret123!'),
                    'role' => UserRole::DOCTOR,
                    'phone' => '+1 (555) ' . rand(100, 999) . '-' . rand(1000, 9999),
                    'avatar_url' => $data['avatar'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $doctor = Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty' => $data['specialty'],
                    'license_number' => $data['license_number'],
                    'bio' => $data['bio'],
                    'consultation_fee' => $data['fee'],
                    'years_of_experience' => $data['experience'],
                    'rating' => $data['rating'],
                    'is_active' => true,
                ]
            );

            // Seed Monday through Friday availability slots (09:00 - 17:00)
            for ($day = 1; $day <= 5; $day++) {
                DoctorAvailability::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00:00',
                    ],
                    [
                        'end_time' => '17:00:00',
                        'slot_duration_minutes' => 30,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
