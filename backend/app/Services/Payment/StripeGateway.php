<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripeGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $webhookSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret', env('STRIPE_SECRET_KEY', 'sk_test_medicon_stripe_mock_key'));
        $this->webhookSecret = config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET', 'whsec_medicon_stripe_mock_secret'));
        $this->baseUrl = 'https://api.stripe.com/v1';
    }

    public function getName(): string
    {
        return 'stripe';
    }

    public function createPaymentIntent(int $amountCents, string $currency, string $paymentMethod = 'card', array $metadata = []): array
    {
        // Stripe Card Fallback
        if ($paymentMethod !== 'card') {
            throw new RuntimeException("Stripe gateway only supports 'card' payments as fallback processor.");
        }

        $params = [
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'payment_method_types' => ['card'],
            'description' => 'Medicon Clinical Visit #' . ($metadata['appointment_id'] ?? 'Booking') . ' (Card Fallback)',
            'metadata' => $metadata,
        ];

        if (app()->environment('production') && !str_starts_with($this->secretKey, 'sk_test_medicon_stripe_mock')) {
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->post("{$this->baseUrl}/payment_intents", $params);

            if ($response->failed()) {
                Log::error('Stripe API Error: ' . $response->body());
                throw new RuntimeException('Stripe Fallback Payment Intent Creation Failed: ' . $response->json('error.message', 'Stripe Error'));
            }

            $data = $response->json();
            return [
                'gateway' => 'stripe',
                'gateway_payment_id' => $data['id'],
                'client_secret' => $data['client_secret'],
                'status' => $data['status'],
                'amount_cents' => $data['amount'],
                'currency' => strtoupper($data['currency']),
                'checkout_url' => null,
            ];
        }

        // Mock deterministic fallback response
        $mockId = 'pi_stripe_' . substr(md5(uniqid('stripe_', true)), 0, 22);
        return [
            'gateway' => 'stripe',
            'gateway_payment_id' => $mockId,
            'client_secret' => $mockId . '_secret_' . substr(md5(uniqid('sec_', true)), 0, 16),
            'status' => 'requires_payment_method',
            'amount_cents' => $amountCents,
            'currency' => strtoupper($currency),
            'checkout_url' => null,
        ];
    }

    public function refund(string $paymentId, int $amountCents, string $reason = 'requested_by_customer'): array
    {
        $params = [
            'payment_intent' => $paymentId,
            'amount' => $amountCents,
            'reason' => 'requested_by_customer',
        ];

        if (app()->environment('production') && !str_starts_with($this->secretKey, 'sk_test_medicon_stripe_mock')) {
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->post("{$this->baseUrl}/refunds", $params);

            if ($response->failed()) {
                Log::error('Stripe Refund Error: ' . $response->body());
                throw new RuntimeException('Stripe Refund Failed: ' . $response->json('error.message', 'Refund Error'));
            }

            $data = $response->json();
            return [
                'success' => true,
                'refund_id' => $data['id'],
                'status' => $data['status'] ?? 'succeeded',
                'amount_cents' => $data['amount'],
            ];
        }

        return [
            'success' => true,
            'refund_id' => 're_stripe_' . substr(md5(uniqid('ref_', true)), 0, 20),
            'status' => 'succeeded',
            'amount_cents' => $amountCents,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($signature)) {
            return false;
        }

        // Parse Stripe-Signature: "t=1492774577,v1=5257a869e7ecebeda32affa62cd49231b2ec357b66e9d161bd306337b30d0201"
        $timestamp = null;
        $v1Sig = null;

        foreach (explode(',', $signature) as $item) {
            $parts = explode('=', trim($item), 2);
            if (count($parts) === 2) {
                if ($parts[0] === 't') $timestamp = $parts[1];
                if ($parts[0] === 'v1') $v1Sig = $parts[1];
            }
        }

        if (!$timestamp || !$v1Sig) {
            $computed = hash_hmac('sha256', $payload, $this->webhookSecret);
            return hash_equals($computed, $signature);
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $computed = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($computed, $v1Sig);
    }
}
