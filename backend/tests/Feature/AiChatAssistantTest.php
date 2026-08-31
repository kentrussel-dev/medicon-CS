<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiChatAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_ai_chat(): void
    {
        $response = $this->postJson('/api/ai/chat', [
            'message' => 'What are the clinic visiting hours?',
        ]);

        $response->assertStatus(401);
    }

    public function test_patient_can_query_ai_chat_with_patient_scoping(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT, 'name' => 'Jane Doe']);
        $patient = Patient::factory()->create([
            'user_id' => $patientUser->id,
            'allergies' => 'Penicillin',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello Jane! Medicon clinics are open Mon-Fri 8:00 AM to 5:00 PM.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($patientUser);
        $response = $this->postJson('/api/ai/chat', [
            'message' => 'What are the clinic visiting hours?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'role' => 'patient',
                'cached' => false,
            ])
            ->assertJsonStructure(['success', 'message', 'role', 'cached', 'timestamp']);
    }

    public function test_doctor_can_query_ai_chat_with_decision_support_scoping(): void
    {
        $doctorUser = User::factory()->doctor()->create(['name' => 'Dr. Sarah Jenkins']);
        $doctor = Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'specialty' => 'Cardiology',
        ]);

        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'SOAP Plan: Lisinopril 10mg PO QD for essential hypertension.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($doctorUser);
        $response = $this->postJson('/api/ai/chat', [
            'message' => 'Draft a SOAP assessment for this patient',
            'patient_id' => $patient->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'role' => 'doctor',
            ]);
    }

    public function test_redis_caches_common_faq_questions_for_subsequent_calls(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        Patient::factory()->create(['user_id' => $patientUser->id]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Visiting hours are 8am to 5pm daily.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($patientUser);

        // First call: Populates Cache
        $response1 = $this->postJson('/api/ai/chat', [
            'message' => 'What are the clinic visiting hours?',
        ]);

        $response1->assertStatus(200)
            ->assertJson(['cached' => false]);

        // Second call: Should return from Redis Cache
        $response2 = $this->postJson('/api/ai/chat', [
            'message' => 'What are the clinic visiting hours?',
        ]);

        $response2->assertStatus(200)
            ->assertJson(['cached' => true, 'message' => 'Visiting hours are 8am to 5pm daily.']);
    }

    public function test_ai_chat_creates_immutable_audit_log(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        Sanctum::actingAs($patientUser);

        $initialLogCount = AuditLog::count();

        $response = $this->postJson('/api/ai/chat', [
            'message' => 'How do I reschedule my appointment?',
        ]);

        $response->assertStatus(200);
        $this->assertEquals($initialLogCount + 1, AuditLog::count());

        $latestLog = AuditLog::latest('id')->first();
        $this->assertEquals(AuditAction::AI_QUERY, $latestLog->action);
        $this->assertEquals($patientUser->id, $latestLog->user_id);
    }

    public function test_gemini_api_failure_returns_graceful_clinical_fallback(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        Patient::factory()->create(['user_id' => $patientUser->id]);

        // Simulate 500 server error from external Gemini API
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response('Service Unavailable', 503),
        ]);

        Sanctum::actingAs($patientUser);

        $response = $this->postJson('/api/ai/chat', [
            'message' => 'How do I book an appointment?',
        ]);

        // Returns HTTP 200 with clear clinical fallback guidance
        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['role' => 'patient']);

        $this->assertNotEmpty($response->json('message'));
    }

    public function test_ai_chat_processes_live_screen_context(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        Patient::factory()->create(['user_id' => $patientUser->id]);

        Sanctum::actingAs($patientUser);

        $response = $this->postJson('/api/ai/chat', [
            'message' => 'Where am I and how do I pay here?',
            'screen_context' => [
                'path' => '/patient/checkout/15',
                'name' => 'patient-checkout',
                'title' => 'Checkout & Payment Gateway',
                'description' => 'Patient is authorizing consultation fee.',
                'details' => ['appointmentId' => 15, 'currency' => 'PHP'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'role' => 'patient',
            ]);

        $this->assertStringContainsString('Payment', $response->json('message'));
    }
}
