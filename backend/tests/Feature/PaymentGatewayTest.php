<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PayMongoGateway;
use App\Services\Payment\PaymentService;
use App\Services\Payment\StripeGateway;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_payment_intent_in_centavos_for_doctor_consultation_fee(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $doctorUser = User::factory()->create(['role' => UserRole::DOCTOR]);
        $doctor = Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'consultation_fee_cents' => 12000, // ₱120.00
        ]);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_fee_cents' => 12000,
            'status' => AppointmentStatus::PENDING,
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($patientUser)->postJson('/api/payments/checkout', [
            'appointment_id' => $appointment->id,
            'payment_method' => 'gcash',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'amount_cents' => 12000,
                    'amount_pesos' => '120.00',
                    'currency' => 'PHP',
                    'gateway' => 'paymongo',
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appointment->id,
            'amount_cents' => 12000,
            'currency' => 'PHP',
            'gateway' => 'paymongo',
            'payment_method' => 'gcash',
        ]);
    }

    public function test_paymongo_webhook_payment_paid_confirms_appointment(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => AppointmentStatus::PENDING,
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'user_id' => $patientUser->id,
            'amount_cents' => 12000,
            'currency' => 'PHP',
            'gateway' => 'paymongo',
            'payment_method' => 'gcash',
            'status' => 'pending',
            'gateway_payment_id' => 'pay_pm_test_99812',
        ]);

        $webhookPayload = [
            'data' => [
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => [
                        'id' => 'pay_pm_test_99812',
                        'attributes' => [
                            'amount' => 12000,
                            'status' => 'paid',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/payments/webhooks/paymongo', $webhookPayload);
        $response->assertStatus(200);

        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals('paid', $appointment->fresh()->payment_status);
        $this->assertEquals(AppointmentStatus::CONFIRMED, $appointment->fresh()->status);
    }

    public function test_paymongo_webhook_payment_failed_marks_payment_failed(): void
    {
        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'user_id' => $patientUser->id,
            'amount_cents' => 12000,
            'currency' => 'PHP',
            'gateway' => 'paymongo',
            'payment_method' => 'gcash',
            'status' => 'pending',
            'gateway_payment_id' => 'pay_pm_test_failed_881',
        ]);

        $webhookPayload = [
            'data' => [
                'attributes' => [
                    'type' => 'payment.failed',
                    'data' => [
                        'id' => 'pay_pm_test_failed_881',
                        'attributes' => [
                            'failed_reason' => 'Insufficient funds in GCash account',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/payments/webhooks/paymongo', $webhookPayload);
        $response->assertStatus(200);

        $this->assertEquals('failed', $payment->fresh()->status);
        $this->assertEquals('failed', $appointment->fresh()->payment_status);
    }

    public function test_cancellation_refund_tiers_full_partial_and_none(): void
    {
        $paymentService = app(PaymentService::class);

        $appointment = new Appointment([
            'consultation_fee_cents' => 12000, // ₱120.00
            'scheduled_start' => Carbon::now()->addHours(48), // 48 hours in advance
        ]);

        // Tier 1: > 24 hours (100% refund)
        $tier1 = $paymentService->calculateCancellationRefund($appointment, Carbon::now());
        $this->assertEquals(1.0, $tier1['refund_rate']);
        $this->assertEquals(12000, $tier1['refund_amount_cents']);
        $this->assertEquals(0, $tier1['cancellation_fee_cents']);

        // Tier 2: 12 to 24 hours (50% refund)
        $appointment->scheduled_start = Carbon::now()->addHours(18);
        $tier2 = $paymentService->calculateCancellationRefund($appointment, Carbon::now());
        $this->assertEquals(0.5, $tier2['refund_rate']);
        $this->assertEquals(6000, $tier2['refund_amount_cents']);
        $this->assertEquals(6000, $tier2['cancellation_fee_cents']);

        // Tier 3: < 12 hours (0% refund)
        $appointment->scheduled_start = Carbon::now()->addHours(4);
        $tier3 = $paymentService->calculateCancellationRefund($appointment, Carbon::now());
        $this->assertEquals(0.0, $tier3['refund_rate']);
        $this->assertEquals(0, $tier3['refund_amount_cents']);
        $this->assertEquals(12000, $tier3['cancellation_fee_cents']);
    }

    public function test_card_payment_transparent_fallback_to_stripe_when_paymongo_fails(): void
    {
        $mockPayMongo = Mockery::mock(PayMongoGateway::class);
        $mockStripe = Mockery::mock(StripeGateway::class);

        // PayMongo throws network / gateway failure on card attempt
        $mockPayMongo->shouldReceive('createPaymentIntent')
            ->once()
            ->andThrow(new RuntimeException('PayMongo Card Gateway Unreachable (503 Service Unavailable)'));

        // Stripe Card Fallback executes successfully
        $mockStripe->shouldReceive('createPaymentIntent')
            ->once()
            ->andReturn([
                'gateway' => 'stripe',
                'gateway_payment_id' => 'pi_stripe_fallback_123',
                'client_secret' => 'pi_stripe_fallback_123_secret',
                'status' => 'requires_payment_method',
                'amount_cents' => 12000,
                'currency' => 'PHP',
                'checkout_url' => null,
            ]);

        $service = new PaymentService($mockPayMongo, $mockStripe);

        $patientUser = User::factory()->create(['role' => UserRole::PATIENT]);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);
        $doctor = Doctor::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_fee_cents' => 12000,
        ]);

        $result = $service->initiatePayment($appointment, $patientUser, 'card');

        $this->assertEquals('stripe', $result['gateway']);
        $this->assertEquals('pi_stripe_fallback_123', $result['gateway_payment_id']);

        $this->assertDatabaseHas('payments', [
            'appointment_id' => $appointment->id,
            'gateway' => 'stripe',
            'payment_method' => 'card',
        ]);
    }
}
