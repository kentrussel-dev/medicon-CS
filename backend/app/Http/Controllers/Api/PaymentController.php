<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create or retrieve payment checkout session for an appointment
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'payment_method' => 'required|string|in:gcash,grab_pay,paymaya,maya,card',
            'amount_cents' => 'nullable|integer|min:100',
        ]);

        $user = $request->user();
        $appointment = Appointment::with(['doctor.user', 'patient.user'])->findOrFail($validated['appointment_id']);

        // Authorization check: User must be the appointment patient or an admin
        if (!$user->isAdmin() && $appointment->patient?->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You may only initiate payments for your own appointments.',
            ], 403);
        }

        try {
            $checkoutData = $this->paymentService->initiatePayment(
                $appointment,
                $user,
                $validated['payment_method'],
                $validated['amount_cents'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment session initialized successfully.',
                'data' => $checkoutData,
            ]);
        } catch (Throwable $e) {
            Log::error('Payment initiation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve payment details and transaction history
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $payment = Payment::with(['appointment.doctor.user', 'user'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'appointment_id' => $payment->appointment_id,
                'amount_cents' => $payment->amount_cents,
                'amount_pesos' => $payment->amount_pesos,
                'currency' => $payment->currency,
                'gateway' => $payment->gateway,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status,
                'gateway_payment_id' => $payment->gateway_payment_id,
                'refund_amount_cents' => $payment->refund_amount_cents,
                'refund_amount_pesos' => $payment->refund_amount_pesos,
                'refunded_at' => $payment->refunded_at?->toIso8601String(),
                'created_at' => $payment->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Handle incoming PayMongo webhooks
     */
    public function handlePayMongoWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Paymongo-Signature', '');

        // Verify webhook signature in production
        if (app()->environment('production') && !$this->paymentService->getPayMongoGateway()->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid PayMongo webhook signature received.');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->json()->all();
        $eventType = $data['data']['attributes']['type'] ?? null;
        $eventData = $data['data']['attributes']['data'] ?? [];

        Log::info("PayMongo webhook event received: {$eventType}");

        switch ($eventType) {
            case 'payment.paid':
                $paymentId = $eventData['id'] ?? ($eventData['attributes']['payment_intent_id'] ?? null);
                if ($paymentId) {
                    $this->paymentService->markAsPaid($paymentId, $data);
                }
                break;

            case 'payment.failed':
                $paymentId = $eventData['id'] ?? null;
                $reason = $eventData['attributes']['failed_reason'] ?? 'Payment authorization failed.';
                if ($paymentId) {
                    $this->paymentService->markAsFailed($paymentId, $reason);
                }
                break;

            case 'payment.refunded':
                $paymentId = $eventData['id'] ?? null;
                if ($paymentId) {
                    $payment = Payment::where('gateway_payment_id', $paymentId)->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'refunded',
                            'refunded_at' => now(),
                        ]);
                    }
                }
                break;

            default:
                Log::info("Unhandled PayMongo webhook event: {$eventType}");
                break;
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle incoming Stripe fallback webhooks
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        if (app()->environment('production') && !$this->paymentService->getStripeGateway()->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid Stripe webhook signature received.');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->json()->all();
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];

        Log::info("Stripe webhook event received: {$type}");

        switch ($type) {
            case 'payment_intent.succeeded':
                $piId = $object['id'] ?? null;
                if ($piId) {
                    $this->paymentService->markAsPaid($piId, $event);
                }
                break;

            case 'payment_intent.payment_failed':
                $piId = $object['id'] ?? null;
                $reason = $object['last_payment_error']['message'] ?? 'Stripe card decline.';
                if ($piId) {
                    $this->paymentService->markAsFailed($piId, $reason);
                }
                break;

            case 'charge.refunded':
                $piId = $object['payment_intent'] ?? null;
                if ($piId) {
                    $payment = Payment::where('gateway_payment_id', $piId)->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'refunded',
                            'refunded_at' => now(),
                        ]);
                    }
                }
                break;
        }

        return response()->json(['received' => true]);
    }
}
