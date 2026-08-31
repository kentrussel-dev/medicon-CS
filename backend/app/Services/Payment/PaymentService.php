<?php

namespace App\Services\Payment;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentService
{
    public function __construct(
        protected PayMongoGateway $payMongoGateway,
        protected StripeGateway $stripeGateway
    ) {}

    /**
     * Create or retrieve an existing pending payment intent for an appointment
     */
    public function initiatePayment(
        Appointment $appointment,
        User $user,
        string $paymentMethod = 'gcash',
        ?int $overrideAmountCents = null
    ): array {
        $amountCents = $overrideAmountCents ?? $appointment->consultation_fee_cents ?? 50000;
        $currency = 'PHP';

        $metadata = [
            'appointment_id' => (string) $appointment->id,
            'user_id' => (string) $user->id,
            'patient_name' => $user->name,
            'doctor_name' => $appointment->doctor?->user?->name ?? 'Attending Clinician',
            'scheduled_start' => $appointment->scheduled_start?->toIso8601String(),
        ];

        $gatewayResponse = null;
        $gatewayUsed = 'paymongo';

        // 1. E-Wallets (GCash, GrabPay, Maya) strictly route through PayMongo
        if (in_array($paymentMethod, ['gcash', 'grab_pay', 'paymaya', 'maya'], true)) {
            $gatewayResponse = $this->payMongoGateway->createPaymentIntent(
                $amountCents,
                $currency,
                $paymentMethod,
                $metadata
            );
            $gatewayUsed = 'paymongo';
        }
        // 2. Card Payments route to PayMongo first, with automatic transparent fallback to Stripe
        else {
            try {
                $gatewayResponse = $this->payMongoGateway->createPaymentIntent(
                    $amountCents,
                    $currency,
                    'card',
                    $metadata
                );
                $gatewayUsed = 'paymongo';
            } catch (Throwable $e) {
                Log::warning("PayMongo Card processing unavailable or failed ({$e->getMessage()}). Triggering Stripe Card fallback.");
                $gatewayResponse = $this->stripeGateway->createPaymentIntent(
                    $amountCents,
                    $currency,
                    'card',
                    $metadata
                );
                $gatewayUsed = 'stripe';
            }
        }

        // 3. Persist / Update Payment Record in Database
        $payment = Payment::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'user_id' => $user->id,
                'amount_cents' => $amountCents,
                'currency' => $currency,
                'gateway' => $gatewayUsed,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'gateway_payment_id' => $gatewayResponse['gateway_payment_id'],
                'gateway_client_secret' => $gatewayResponse['client_secret'] ?? null,
                'metadata' => array_merge($metadata, [
                    'gateway_used' => $gatewayUsed,
                    'checkout_url' => $gatewayResponse['checkout_url'] ?? null,
                ]),
            ]
        );

        $appointment->update([
            'payment_status' => 'pending',
            'consultation_fee_cents' => $amountCents,
        ]);

        return [
            'payment_id' => $payment->id,
            'gateway' => $gatewayUsed,
            'gateway_payment_id' => $gatewayResponse['gateway_payment_id'],
            'client_secret' => $gatewayResponse['client_secret'] ?? null,
            'checkout_url' => $gatewayResponse['checkout_url'] ?? null,
            'amount_cents' => $amountCents,
            'amount_pesos' => number_format($amountCents / 100, 2),
            'currency' => $currency,
            'status' => $payment->status,
        ];
    }

    /**
     * Mark payment as successfully paid and confirm the appointment
     */
    public function markAsPaid(string $gatewayPaymentId, ?array $payload = null): ?Payment
    {
        $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)->first();

        if (!$payment) {
            Log::warning("Payment record with gateway ID {$gatewayPaymentId} not found during webhook fulfillment.");
            return null;
        }

        return DB::transaction(function () use ($payment, $payload) {
            $payment->update([
                'status' => 'paid',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'paid_at' => now()->toIso8601String(),
                    'webhook_payload' => $payload,
                ]),
            ]);

            $appointment = $payment->appointment;
            if ($appointment) {
                $appointment->update([
                    'payment_status' => 'paid',
                    'status' => \App\Enums\AppointmentStatus::CONFIRMED,
                ]);
            }

            return $payment;
        });
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $gatewayPaymentId, ?string $reason = null): ?Payment
    {
        $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)->first();

        if (!$payment) {
            return null;
        }

        $payment->update([
            'status' => 'failed',
            'metadata' => array_merge($payment->metadata ?? [], [
                'failed_at' => now()->toIso8601String(),
                'failure_reason' => $reason,
            ]),
        ]);

        if ($payment->appointment) {
            $payment->appointment->update(['payment_status' => 'failed']);
        }

        return $payment;
    }

    /**
     * Calculate cancellation refund rate based on how far in advance the cancellation occurs
     *
     * Window rules:
     * - > 24 hours in advance: 100% refund
     * - 12 to 24 hours in advance: 50% refund
     * - < 12 hours in advance: 0% refund
     *
     * @return array ['refund_rate' => float, 'refund_amount_cents' => int, 'cancellation_fee_cents' => int, 'window' => string]
     */
    public function calculateCancellationRefund(Appointment $appointment, ?Carbon $cancellationTime = null): array
    {
        $now = $cancellationTime ?? Carbon::now();
        $start = $appointment->scheduled_start;

        $payment = $appointment->payment;
        $totalPaidCents = $payment ? $payment->amount_cents : ($appointment->consultation_fee_cents ?? 50000);

        if (!$start || $now->gte($start)) {
            // Already started or in the past
            return [
                'refund_rate' => 0.0,
                'refund_amount_cents' => 0,
                'cancellation_fee_cents' => $totalPaidCents,
                'hours_notice' => 0,
                'tier' => 'immediate_no_refund',
            ];
        }

        $hoursNotice = $now->diffInHours($start, false);

        if ($hoursNotice >= 24) {
            $refundRate = 1.0;
            $tier = 'full_refund_24h';
        } elseif ($hoursNotice >= 12) {
            $refundRate = 0.5;
            $tier = 'partial_refund_12h_24h';
        } else {
            $refundRate = 0.0;
            $tier = 'no_refund_under_12h';
        }

        $refundAmountCents = (int) round($totalPaidCents * $refundRate);
        $cancellationFeeCents = $totalPaidCents - $refundAmountCents;

        return [
            'refund_rate' => $refundRate,
            'refund_amount_cents' => $refundAmountCents,
            'cancellation_fee_cents' => $cancellationFeeCents,
            'hours_notice' => $hoursNotice,
            'tier' => $tier,
        ];
    }

    /**
     * Process appointment cancellation refund via the appropriate gateway
     */
    public function processCancellationRefund(Appointment $appointment, string $reason = 'patient_cancelled'): array
    {
        $payment = $appointment->payment;

        if (!$payment || !$payment->isPaid()) {
            return [
                'refunded' => false,
                'reason' => 'No completed payment to refund.',
                'refund_amount_cents' => 0,
            ];
        }

        $calc = $this->calculateCancellationRefund($appointment);
        $refundAmountCents = $calc['refund_amount_cents'];

        if ($refundAmountCents <= 0) {
            $payment->update([
                'cancellation_fee_cents' => $calc['cancellation_fee_cents'],
                'metadata' => array_merge($payment->metadata ?? [], [
                    'cancellation_refund_calc' => $calc,
                ]),
            ]);

            return [
                'refunded' => false,
                'reason' => 'Cancellation made within non-refundable window (< 12 hours).',
                'refund_amount_cents' => 0,
                'cancellation_fee_cents' => $calc['cancellation_fee_cents'],
            ];
        }

        $gateway = $payment->gateway === 'stripe' ? $this->stripeGateway : $this->payMongoGateway;

        try {
            $gatewayResponse = $gateway->refund(
                $payment->gateway_payment_id,
                $refundAmountCents,
                $reason
            );

            $payment->update([
                'status' => 'refunded',
                'refund_amount_cents' => $refundAmountCents,
                'cancellation_fee_cents' => $calc['cancellation_fee_cents'],
                'refunded_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'cancellation_refund_calc' => $calc,
                    'gateway_refund_response' => $gatewayResponse,
                ]),
            ]);

            $appointment->update(['payment_status' => 'refunded']);

            return [
                'refunded' => true,
                'refund_amount_cents' => $refundAmountCents,
                'refund_amount_pesos' => number_format($refundAmountCents / 100, 2),
                'cancellation_fee_cents' => $calc['cancellation_fee_cents'],
                'tier' => $calc['tier'],
            ];
        } catch (Throwable $e) {
            Log::error("Refund execution failed: {$e->getMessage()}");
            throw $e;
        }
    }

    public function getPayMongoGateway(): PayMongoGateway
    {
        return $this->payMongoGateway;
    }

    public function getStripeGateway(): StripeGateway
    {
        return $this->stripeGateway;
    }
}
