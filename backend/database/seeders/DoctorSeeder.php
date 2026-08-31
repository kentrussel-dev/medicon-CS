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
                'name' => 'Dr. Sarah Jenkins, MD, FACC',
                'email' => 'sarah.jenkins@medicon.health',
                'specialty' => 'Cardiology',
                'license_number' => 'MD-CAR-88210',
                'bio' => 'Harvard Medical School alumna specializing in preventative cardiology, cardiac arrhythmias, echocardiography, and remote vital monitoring.',
                'fee' => 1500.00,
                'fee_cents' => 150000,
                'experience' => 14,
                'rating' => 4.96,
                'avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Marcus Chen, MD, PhD',
                'email' => 'marcus.chen@medicon.health',
                'specialty' => 'Neurology',
                'license_number' => 'MD-NEU-41903',
                'bio' => 'Board-certified Neurologist focusing on chronic migraine management, cognitive assessment, neuromuscular disorders, and tele-stroke triage.',
                'fee' => 1200.00,
                'fee_cents' => 120000,
                'experience' => 10,
                'rating' => 4.91,
                'avatar' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Elena Rostova, MD',
                'email' => 'elena.rostova@medicon.health',
                'specialty' => 'Dermatology',
                'license_number' => 'MD-DER-33918',
                'bio' => 'Clinical dermatologist dedicated to teledermatology, eczema and psoriasis protocols, autoimmune skin conditions, and early skin lesion assessment.',
                'fee' => 800.00,
                'fee_cents' => 80000,
                'experience' => 8,
                'rating' => 4.93,
                'avatar' => 'https://images.unsplash.com/photo-1594824813689-53b53c7c25a0?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. James Wilson, MD',
                'email' => 'james.wilson@medicon.health',
                'specialty' => 'General Practice',
                'license_number' => 'MD-GEN-77401',
                'bio' => 'Primary care physician providing comprehensive family health, chronic disease management, metabolic screenings, and routine clinical checkups.',
                'fee' => 500.00,
                'fee_cents' => 50000,
                'experience' => 16,
                'rating' => 4.89,
                'avatar' => 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Aisha Patel, MD',
                'email' => 'aisha.patel@medicon.health',
                'specialty' => 'Psychiatry',
                'license_number' => 'MD-PSY-92014',
                'bio' => 'Adult psychiatrist providing compassionate behavioral health, depression and anxiety management, psychopharmacology, and stress mitigation therapy.',
                'fee' => 1800.00,
                'fee_cents' => 180000,
                'experience' => 11,
                'rating' => 4.98,
                'avatar' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Dr. Robert Taylor, MD',
                'email' => 'robert.taylor@medicon.health',
                'specialty' => 'Orthopedics',
                'license_number' => 'MD-ORT-50119',
                'bio' => 'Orthopedic surgeon and musculoskeletal specialist focusing on sports injuries, joint rehabilitation, osteoarthritis, and pre-op evaluation.',
                'fee' => 1250.00,
                'fee_cents' => 125000,
                'experience' => 13,
                'rating' => 4.92,
                'avatar' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=150&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($doctorsData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Secret123!'),
                    'role' => UserRole::DOCTOR,
                    'phone' => '+63 917 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                    'avatar_url' => $data['avatar'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            $doctor = Doctor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty' => $data['specialty'],
                    'license_number' => $data['license_number'],
                    'bio' => $data['bio'],
                    'consultation_fee' => $data['fee'],
                    'consultation_fee_cents' => $data['fee_cents'],
                    'years_of_experience' => $data['experience'],
                    'rating' => $data['rating'],
                    'is_active' => true,
                ]
            );

            // Seed Monday - Friday 09:00 - 17:00 availability
            for ($day = 1; $day <= 5; $day++) {
                DoctorAvailability::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
