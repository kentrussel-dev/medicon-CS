<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patientsData = [
            [
                'name' => 'John Doe',
                'email' => 'patient@medicon.health',
                'dob' => '1984-06-15',
                'gender' => 'M',
                'blood' => 'O+',
                'allergies' => 'Penicillin, Shellfish',
                'notes' => 'Patient has mild essential hypertension and seasonal rhinitis.',
                'scholarship' => false,
                'hypertension' => true,
                'diabetes' => false,
                'alcoholism' => false,
                'handicap' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Emily Clark',
                'email' => 'emily.clark@medicon.health',
                'dob' => '1992-11-23',
                'gender' => 'F',
                'blood' => 'A+',
                'allergies' => 'Latex',
                'notes' => 'History of migraine headaches triggered by stress and sleep deprivation.',
                'scholarship' => true,
                'hypertension' => false,
                'diabetes' => false,
                'alcoholism' => false,
                'handicap' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Robert Vance',
                'email' => 'robert.vance@medicon.health',
                'dob' => '1968-03-08',
                'gender' => 'M',
                'blood' => 'B+',
                'allergies' => 'Sulfa Drugs',
                'notes' => 'Type 2 Diabetes Mellitus managed with Metformin, hypertensive retinopathy under observation.',
                'scholarship' => false,
                'hypertension' => true,
                'diabetes' => true,
                'alcoholism' => false,
                'handicap' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Lisa Martinez',
                'email' => 'lisa.martinez@medicon.health',
                'dob' => '1995-08-30',
                'gender' => 'F',
                'blood' => 'AB-',
                'allergies' => 'None known',
                'notes' => 'Annual wellness examination follow-up and teledermatology evaluation.',
                'scholarship' => false,
                'hypertension' => false,
                'diabetes' => false,
                'alcoholism' => false,
                'handicap' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($patientsData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Secret123!'),
                    'role' => UserRole::PATIENT,
                    'phone' => '+1 (555) ' . rand(100, 999) . '-' . rand(1000, 9999),
                    'avatar_url' => $data['avatar'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'date_of_birth' => $data['dob'],
                    'gender' => $data['gender'],
                    'blood_type' => $data['blood'],
                    'emergency_contact_name' => 'Primary Emergency Contact',
                    'emergency_contact_phone' => '+1 (555) 999-0011',
                    'allergies' => $data['allergies'],
                    'medical_notes' => $data['notes'],
                    'insurance_provider' => 'Blue Cross Blue Shield',
                    'insurance_policy_number' => 'BCBS-' . rand(100000, 999999),
                    'scholarship' => $data['scholarship'],
                    'hypertension' => $data['hypertension'],
                    'diabetes' => $data['diabetes'],
                    'alcoholism' => $data['alcoholism'],
                    'handicap_level' => $data['handicap'],
                ]
            );
        }
    }
}
