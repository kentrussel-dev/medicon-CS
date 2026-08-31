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
                'name' => 'Jane Doe',
                'email' => 'patient@medicon.health',
                'dob' => '1995-04-12',
                'gender' => 'F',
                'blood' => 'O+',
                'allergies' => 'Penicillin, Sulfa Drugs',
                'emergency_name' => 'Mark Doe (Spouse)',
                'emergency_phone' => '+1 (555) 019-9831',
                'notes' => 'Patient has mild essential hypertension and seasonal rhinitis. Regular exercise protocol initiated.',
                'scholarship' => false,
                'hypertension' => true,
                'diabetes' => false,
                'alcoholism' => false,
                'handicap' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'John Miller',
                'email' => 'john.miller@medicon.health',
                'dob' => '1979-08-25',
                'gender' => 'M',
                'blood' => 'A+',
                'allergies' => 'Latex, Aspirin',
                'emergency_name' => 'Sarah Miller (Wife)',
                'emergency_phone' => '+1 (555) 014-4920',
                'notes' => 'Type 2 Diabetes Mellitus under Metformin control. Regular HbA1c screening recommended.',
                'scholarship' => false,
                'hypertension' => true,
                'diabetes' => true,
                'alcoholism' => false,
                'handicap' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Emily Clark',
                'email' => 'emily.clark@medicon.health',
                'dob' => '1998-11-23',
                'gender' => 'F',
                'blood' => 'B+',
                'allergies' => 'None known',
                'emergency_name' => 'David Clark (Brother)',
                'emergency_phone' => '+1 (555) 018-8314',
                'notes' => 'History of episodic migraines with visual aura. Sumatriptan prescribed as acute abortive therapy.',
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
                'dob' => '1966-03-08',
                'gender' => 'M',
                'blood' => 'AB+',
                'allergies' => 'Codeine, Shellfish',
                'emergency_name' => 'Clara Vance (Sister)',
                'emergency_phone' => '+1 (555) 012-2849',
                'notes' => 'Coronary artery disease history, stage 2 hypertension under dual ACE-inhibitor and beta-blocker therapy.',
                'scholarship' => false,
                'hypertension' => true,
                'diabetes' => false,
                'alcoholism' => false,
                'handicap' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Lisa Martinez',
                'email' => 'lisa.martinez@medicon.health',
                'dob' => '2001-09-14',
                'gender' => 'F',
                'blood' => 'O-',
                'allergies' => 'None known',
                'emergency_name' => 'Elena Martinez (Mother)',
                'emergency_phone' => '+1 (555) 016-6029',
                'notes' => 'Atopic dermatitis follow-up and annual preventive health examination.',
                'scholarship' => false,
                'hypertension' => false,
                'diabetes' => false,
                'alcoholism' => false,
                'handicap' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($patientsData as $data) {
            $user = User::updateOrCreate(
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

            Patient::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'date_of_birth' => $data['dob'],
                    'gender' => $data['gender'],
                    'blood_type' => $data['blood'],
                    'emergency_contact_name' => $data['emergency_name'],
                    'emergency_contact_phone' => $data['emergency_phone'],
                    'allergies' => $data['allergies'],
                    'medical_notes' => $data['notes'],
                    'has_scholarship' => $data['scholarship'],
                    'has_hypertension' => $data['hypertension'],
                    'has_diabetes' => $data['diabetes'],
                    'has_alcoholism' => $data['alcoholism'],
                    'handicap_level' => $data['handicap'],
                ]
            );
        }
    }
}
