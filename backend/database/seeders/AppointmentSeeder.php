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

        $patientJohn = $patients->first();
        $patientEmily = $patients->skip(1)->first() ?? $patients->first();
        $patientRobert = $patients->skip(2)->first() ?? $patients->first();

        // 1. Past Completed Appointment with John Doe
        Appointment::create([
            'patient_id' => $patientJohn->id,
            'doctor_id' => $docSarah->id,
            'scheduled_start' => Carbon::now()->subDays(14)->setTime(10, 0),
            'scheduled_end' => Carbon::now()->subDays(14)->setTime(10, 30),
            'status' => AppointmentStatus::COMPLETED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Quarterly cardiovascular review and blood pressure assessment',
            'notes' => 'Patient joined on time. Video call stable.',
            'no_show_risk_score' => 0.1245,
            'no_show_risk_level' => RiskLevel::LOW,
            'risk_factors' => ['Strong attendance profile with low risk indicators'],
            'is_reminder_sent' => true,
            'meeting_link' => 'https://meet.medicon.health/room/demo-completed-1',
        ]);

        // 2. Past Completed Appointment with Emily Clark
        Appointment::create([
            'patient_id' => $patientEmily->id,
            'doctor_id' => $docMarcus->id,
            'scheduled_start' => Carbon::now()->subDays(7)->setTime(14, 0),
            'scheduled_end' => Carbon::now()->subDays(7)->setTime(14, 30),
            'status' => AppointmentStatus::COMPLETED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Severe episodic migraine consultation and preventive care plan',
            'notes' => 'Prescribed Sumatriptan for acute aura episodes.',
            'no_show_risk_score' => 0.2810,
            'no_show_risk_level' => RiskLevel::LOW,
            'risk_factors' => ['Moderate lead time (7 days)'],
            'is_reminder_sent' => true,
            'meeting_link' => 'https://meet.medicon.health/room/demo-completed-2',
        ]);

        // 3. Upcoming Confirmed Appointment (Tomorrow) - Low Risk
        Appointment::create([
            'patient_id' => $patientJohn->id,
            'doctor_id' => $docElena->id,
            'scheduled_start' => Carbon::now()->addDays(1)->setTime(11, 0),
            'scheduled_end' => Carbon::now()->addDays(1)->setTime(11, 30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Contact dermatitis follow-up and topical corticosteroid adjustment',
            'notes' => 'Patient reports improved skin redness.',
            'no_show_risk_score' => 0.1850,
            'no_show_risk_level' => RiskLevel::LOW,
            'risk_factors' => ['Short lead time (1 day)', 'SMS reminder sent'],
            'is_reminder_sent' => true,
            'meeting_link' => 'https://meet.medicon.health/room/demo-upcoming-low',
        ]);

        // 4. Upcoming High-Risk Flagged Appointment (ML Alert)
        Appointment::create([
            'patient_id' => $patientRobert->id,
            'doctor_id' => $docSarah->id,
            'scheduled_start' => Carbon::now()->addDays(12)->setTime(15, 0),
            'scheduled_end' => Carbon::now()->addDays(12)->setTime(15, 30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Uncontrolled hypertension consultation and medication review',
            'notes' => 'Flagged for proactive outreach by medical assistant.',
            'no_show_risk_score' => 0.7420,
            'no_show_risk_level' => RiskLevel::HIGH,
            'risk_factors' => [
                'High booking lead time (12 days)',
                'History of missed appointments in prior clinic',
                'Friday afternoon schedule slot',
            ],
            'is_reminder_sent' => false,
            'meeting_link' => 'https://meet.medicon.health/room/demo-upcoming-highrisk',
        ]);

        // 5. Cancelled Appointment
        Appointment::create([
            'patient_id' => $patientEmily->id,
            'doctor_id' => $docElena->id,
            'scheduled_start' => Carbon::now()->subDays(3)->setTime(9, 30),
            'scheduled_end' => Carbon::now()->subDays(3)->setTime(10, 0),
            'status' => AppointmentStatus::CANCELLED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Routine mole check',
            'cancellation_reason' => 'Work schedule conflict. Rescheduled for next month.',
            'no_show_risk_score' => 0.4200,
            'no_show_risk_level' => RiskLevel::MEDIUM,
            'risk_factors' => ['Moderate lead time'],
            'is_reminder_sent' => true,
        ]);
    }
}
