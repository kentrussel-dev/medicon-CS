<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LiveKitTelehealthAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_telehealth_token(): void
    {
        $response = $this->getJson('/api/appointments/1/telehealth/token');
        $response->assertStatus(401);
    }

    public function test_assigned_patient_can_generate_valid_telehealth_token(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT, 'name' => 'Jane Doe']);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->doctor()->create(['name' => 'Dr. Sarah Jenkins']);
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addMinutes(30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Cardiology Telehealth Consultation',
        ]);

        Sanctum::actingAs($patientUser);

        $response = $this->getJson("/api/appointments/{$appointment->id}/telehealth/token");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'session' => [
                    'room_name' => "medicon_room_appt_{$appointment->id}",
                    'role' => 'PATIENT',
                    'is_host' => false,
                ],
            ])
            ->assertJsonStructure(['success', 'appointment', 'session' => ['token', 'livekit_url', 'identity']]);

        // Verify token is a valid 3-part JWT
        $token = $response->json('session.token');
        $this->assertCount(3, explode('.', $token));
    }

    public function test_assigned_doctor_can_generate_valid_telehealth_token(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addMinutes(30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Telehealth Consultation',
        ]);

        Sanctum::actingAs($doctorUser);

        $response = $this->getJson("/api/appointments/{$appointment->id}/telehealth/token");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'session' => [
                    'room_name' => "medicon_room_appt_{$appointment->id}",
                    'role' => 'DOCTOR',
                    'is_host' => true,
                ],
            ]);
    }

    public function test_unauthorized_stranger_cannot_generate_token_for_other_appointment(): void
    {
        $patient1User = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient1 = Patient::factory()->create(['user_id' => $patient1User->id]);

        $patient2User = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient2 = Patient::factory()->create(['user_id' => $patient2User->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $appointment = Appointment::create([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addMinutes(30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Confidential Consultation',
        ]);

        // Patient 1 tries to get a LiveKit token for Patient 2's appointment
        Sanctum::actingAs($patient1User);

        $response = $this->getJson("/api/appointments/{$appointment->id}/telehealth/token");

        $response->assertStatus(403);
    }

    public function test_attending_doctor_can_add_specialist_participant(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addMinutes(30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Multi-party consultation',
        ]);

        Sanctum::actingAs($doctorUser);

        $response = $this->postJson("/api/appointments/{$appointment->id}/telehealth/participants", [
            'name' => 'Dr. Marcus Chen (Neurology Specialist)',
            'role' => 'specialist',
            'email' => 'marcus.chen@medicon.health',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'participant' => [
                    'name' => 'Dr. Marcus Chen (Neurology Specialist)',
                    'role' => 'specialist',
                ],
                'session' => [
                    'role' => 'SPECIALIST',
                    'is_host' => false,
                ],
            ]);

        $this->assertDatabaseHas('appointment_participants', [
            'appointment_id' => $appointment->id,
            'name' => 'Dr. Marcus Chen (Neurology Specialist)',
            'role' => 'specialist',
        ]);
    }

    public function test_patient_cannot_add_participants_to_appointment(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addMinutes(30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Consultation',
        ]);

        Sanctum::actingAs($patientUser);

        $response = $this->postJson("/api/appointments/{$appointment->id}/telehealth/participants", [
            'name' => 'Unapproved Guest',
            'role' => 'specialist',
        ]);

        $response->assertStatus(403);
    }

    public function test_telehealth_join_creates_hipaa_audit_log(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->doctor()->create();
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addMinutes(30),
            'status' => AppointmentStatus::CONFIRMED,
            'type' => AppointmentType::TELEHEALTH,
            'reason' => 'Consultation',
        ]);

        Sanctum::actingAs($patientUser);

        $initialLogs = AuditLog::count();

        $response = $this->getJson("/api/appointments/{$appointment->id}/telehealth/token");
        $response->assertStatus(200);

        $this->assertEquals($initialLogs + 1, AuditLog::count());

        $log = AuditLog::latest('id')->first();
        $this->assertEquals(AuditAction::TELEHEALTH_JOIN, $log->action);
        $this->assertEquals($appointment->id, $log->record_id);
    }
}
