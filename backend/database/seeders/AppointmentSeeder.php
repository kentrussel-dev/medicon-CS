<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\RiskLevel;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            return;
        }

        $docSarah = $doctors->where('specialty', 'Cardiology')->first() ?? $doctors->first();
        $docMarcus = $doctors->where('specialty', 'Neurology')->first() ?? $doctors->skip(1)->first();
        $docElena = $doctors->where('specialty', 'Dermatology')->first() ?? $doctors->skip(2)->first();
        $docWilson = $doctors->where('specialty', 'General Practice')->first() ?? $doctors->skip(3)->first();

        $patientJane = $patients->first();
        $patientJohn = $patients->skip(1)->first() ?? $patients->first();
        $patientEmily = $patients->skip(2)->first() ?? $patients->first();
        $patientRobert = $patients->skip(3)->first() ?? $patients->first();

        // 1. Live Active Telehealth Room (Jane Doe + Dr. Sarah Jenkins) - Code: sdf-sdyy-125
        Appointment::updateOrCreate(
            ['id' => 1],
            [
                'patient_id' => $patientJane->id,
                'doctor_id' => $docSarah->id,
                'scheduled_start' => Carbon::now()->setTime(10, 0),
                'scheduled_end' => Carbon::now()->setTime(10, 30),
                'status' => AppointmentStatus::CONFIRMED,
                'type' => AppointmentType::TELEHEALTH,
                'room_code' => 'sdf-sdyy-125',
                'reason' => 'Cardiovascular Follow-up & Blood Pressure Regulation Review',
                'notes' => 'Multi-party consultation with Dr. Marcus Chen (Neurology Specialist) participating.',
                'no_show_risk_score' => 0.0820,
                'no_show_risk_level' => RiskLevel::LOW,
                'risk_factors' => ['High patient engagement', 'SMS confirmed 2 hours prior'],
                'is_reminder_sent' => true,
                'meeting_link' => 'https://meet.medicon.health/telehealth/room/sdf-sdyy-125',
            ]
        );

        // 2. Upcoming Neurology Visit (Emily Clark + Dr. Marcus Chen) - Code: neu-512-304
        Appointment::updateOrCreate(
            ['id' => 2],
            [
                'patient_id' => $patientEmily->id,
                'doctor_id' => $docMarcus->id,
                'scheduled_start' => Carbon::now()->addDays(2)->setTime(14, 0),
                'scheduled_end' => Carbon::now()->addDays(2)->setTime(14, 30),
                'status' => AppointmentStatus::CONFIRMED,
                'type' => AppointmentType::TELEHEALTH,
                'room_code' => 'neu-512-304',
                'reason' => 'Severe Episodic Migraine Consultation & Preventive Care Plan',
                'notes' => 'Evaluate visual aura frequency and assess Sumatriptan response.',
                'no_show_risk_score' => 0.1450,
                'no_show_risk_level' => RiskLevel::LOW,
                'risk_factors' => ['Direct follow-up visit', 'Digital calendar synced'],
                'is_reminder_sent' => true,
                'meeting_link' => 'https://meet.medicon.health/telehealth/room/neu-512-304',
            ]
        );

        // 3. Upcoming Dermatology Follow-up (Jane Doe + Dr. Elena Rostova) - Code: der-881-209
        Appointment::updateOrCreate(
            ['id' => 3],
            [
                'patient_id' => $patientJane->id,
                'doctor_id' => $docElena->id,
                'scheduled_start' => Carbon::now()->addDays(5)->setTime(11, 0),
                'scheduled_end' => Carbon::now()->addDays(5)->setTime(11, 30),
                'status' => AppointmentStatus::CONFIRMED,
                'type' => AppointmentType::TELEHEALTH,
                'room_code' => 'der-881-209',
                'reason' => 'Atopic Dermatitis & Contact Eczema Progress Assessment',
                'notes' => 'Review response to topical corticosteroid therapy.',
                'no_show_risk_score' => 0.1850,
                'no_show_risk_level' => RiskLevel::LOW,
                'risk_factors' => ['Routine dermatology check'],
                'is_reminder_sent' => true,
                'meeting_link' => 'https://meet.medicon.health/telehealth/room/der-881-209',
            ]
        );

        // 4. Upcoming High-Risk Flagged Appointment (Robert Vance + Dr. Wilson) - Code: gen-104-550
        Appointment::updateOrCreate(
            ['id' => 4],
            [
                'patient_id' => $patientRobert->id,
                'doctor_id' => $docWilson->id,
                'scheduled_start' => Carbon::now()->addDays(10)->setTime(15, 0),
                'scheduled_end' => Carbon::now()->addDays(10)->setTime(15, 30),
                'status' => AppointmentStatus::CONFIRMED,
                'type' => AppointmentType::TELEHEALTH,
                'room_code' => 'gen-104-550',
                'reason' => 'Chronic Metabolic Panel & Glycemic Control Checkup',
                'notes' => 'Flagged for proactive outreach by clinical nurse coordinator.',
                'no_show_risk_score' => 0.7420,
                'no_show_risk_level' => RiskLevel::HIGH,
                'risk_factors' => [
                    'Extended lead time (10 days)',
                    'History of missed follow-ups',
                    'Late afternoon Friday slot',
                ],
                'is_reminder_sent' => false,
                'meeting_link' => 'https://meet.medicon.health/telehealth/room/gen-104-550',
            ]
        );

        // 5. Past Completed Cardiology Consultation (Jane Doe + Dr. Sarah Jenkins)
        Appointment::updateOrCreate(
            ['id' => 5],
            [
                'patient_id' => $patientJane->id,
                'doctor_id' => $docSarah->id,
                'scheduled_start' => Carbon::now()->subDays(21)->setTime(10, 0),
                'scheduled_end' => Carbon::now()->subDays(21)->setTime(10, 30),
                'status' => AppointmentStatus::COMPLETED,
                'type' => AppointmentType::TELEHEALTH,
                'room_code' => 'car-192-800',
                'reason' => 'Initial Cardiology Telehealth Evaluation & Baseline ECG Review',
                'notes' => 'Concluded with Atorvastatin and Lisinopril prescription issuance.',
                'no_show_risk_score' => 0.1200,
                'no_show_risk_level' => RiskLevel::LOW,
                'risk_factors' => ['Patient joined promptly on time'],
                'is_reminder_sent' => true,
                'meeting_link' => 'https://meet.medicon.health/telehealth/room/car-192-800',
            ]
        );
    }
}
