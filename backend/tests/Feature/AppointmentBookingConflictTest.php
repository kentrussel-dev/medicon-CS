<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentBookingConflictTest extends TestCase
{
    use RefreshDatabase;

    protected User $patientUser;
    protected Patient $patient;
    protected User $doctorUser;
    protected Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $this->patient = Patient::factory()->create(['user_id' => $this->patientUser->id]);

        $this->doctorUser = User::factory()->doctor()->create();
        $this->doctor = Doctor::factory()->create(['user_id' => $this->doctorUser->id]);

        // Add doctor availability for Wednesday (day 3) 09:00 - 17:00
        DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    public function test_patient_can_book_valid_appointment_slot(): void
    {
        Sanctum::actingAs($this->patientUser);

        // Next Wednesday at 10:00 AM
        $start = Carbon::now()->next(Carbon::WEDNESDAY)->setTime(10, 0, 0);
        $end = $start->copy()->addMinutes(30);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $this->doctor->id,
            'scheduled_start' => $start->toIso8601String(),
            'scheduled_end' => $end->toIso8601String(),
            'reason' => 'Annual physical examination and blood pressure review',
            'type' => 'TELEHEALTH',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('appointment.status', 'CONFIRMED')
            ->assertJsonPath('appointment.doctor_id', $this->doctor->id);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);
    }

    public function test_cannot_book_overlapping_appointment_for_same_doctor(): void
    {
        $start = Carbon::now()->next(Carbon::WEDNESDAY)->setTime(11, 0, 0);
        $end = $start->copy()->addMinutes(30);

        // Create an existing appointment
        Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_start' => $start,
            'scheduled_end' => $end,
            'status' => AppointmentStatus::CONFIRMED,
            'type' => \App\Enums\AppointmentType::TELEHEALTH,
            'reason' => 'Existing consultation',
        ]);

        // Another patient attempts to book the same slot
        $otherPatientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $otherPatient = Patient::factory()->create(['user_id' => $otherPatientUser->id]);
        Sanctum::actingAs($otherPatientUser);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $this->doctor->id,
            'scheduled_start' => $start->toIso8601String(),
            'scheduled_end' => $end->toIso8601String(),
            'reason' => 'Conflicting appointment attempt',
            'type' => 'TELEHEALTH',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_start']);
    }

    public function test_cannot_book_appointment_outside_doctor_working_hours(): void
    {
        Sanctum::actingAs($this->patientUser);

        // Sunday booking (no availability configured for Sunday)
        $start = Carbon::now()->next(Carbon::SUNDAY)->setTime(10, 0, 0);
        $end = $start->copy()->addMinutes(30);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $this->doctor->id,
            'scheduled_start' => $start->toIso8601String(),
            'scheduled_end' => $end->toIso8601String(),
            'reason' => 'Out of hours booking',
            'type' => 'TELEHEALTH',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_start']);
    }

    public function test_cannot_book_appointment_in_the_past(): void
    {
        Sanctum::actingAs($this->patientUser);

        $start = Carbon::now()->subDays(2)->setTime(10, 0, 0);
        $end = $start->copy()->addMinutes(30);

        $response = $this->postJson('/api/appointments', [
            'doctor_id' => $this->doctor->id,
            'scheduled_start' => $start->toIso8601String(),
            'scheduled_end' => $end->toIso8601String(),
            'reason' => 'Past booking attempt',
            'type' => 'TELEHEALTH',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scheduled_start']);
    }
}
