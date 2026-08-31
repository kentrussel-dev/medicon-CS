<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayMongoGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $publicKey;
    protected string $webhookSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key', env('PAYMONGO_SECRET_KEY', 'sk_test_medicon_paymongo_mock_key'));
        $this->publicKey = config('services.paymongo.public_key', env('PAYMONGO_PUBLIC_KEY', 'pk_test_medicon_paymongo_mock_key'));
        $this->webhookSecret = config('services.paymongo.webhook_secret', env('PAYMONGO_WEBHOOK_SECRET', 'whsec_medicon_paymongo_mock_secret'));
        $this->baseUrl = config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
    }

    public function getName(): string
    {
        return 'paymongo';
    }

    /**
     * Map internal payment methods to PayMongo payment method types
     */
    protected function mapPaymentMethod(string $method): array
    {
        return match ($method) {
            'gcash' => ['gcash'],
            'grab_pay' => ['grab_pay'],
            'paymaya', 'maya' => ['paymaya'],
            'card' => ['card'],
            default => ['gcash', 'grab_pay', 'paymaya', 'card'],
        };
    }

    public function createPaymentIntent(int $amountCents, string $currency, string $paymentMethod, array $metadata = []): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $amountCents,
                    'payment_method_allowed' => $this->mapPaymentMethod($paymentMethod),
                    'payment_method_options' => [
                        'card' => [
                            'request_three_d_secure' => 'any',
                        ],
                    ],
                    'currency' => strtoupper($currency),
                    'description' => 'Medicon Clinical Consultation #' . ($metadata['appointment_id'] ?? 'Booking'),
                    'statement_descriptor' => 'MEDICON CLINICAL',
                    'metadata' => $metadata,
                ],
            ],
        ];

        // In production / live environment: Dispatch HTTP request to PayMongo
        // In local / sandbox environment: Return structured PayMongo Payment Intent
        if (app()->environment('production') && !str_starts_with($this->secretKey, 'sk_test_medicon_mock')) {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/payment_intents", $payload);

            if ($response->failed()) {
                Log::error('PayMongo API Error: ' . $response->body());
                throw new RuntimeException('PayMongo Payment Intent Creation Failed: ' . $response->json('errors.0.detail', 'Gateway Error'));
            }

            $data = $response->json('data');
            return [
                'gateway' => 'paymongo',
                'gateway_payment_id' => $data['id'],
                'client_secret' => $data['attributes']['client_key'] ?? null,
                'status' => $data['attributes']['status'],
                'amount_cents' => $data['attributes']['amount'],
                'currency' => $data['attributes']['currency'],
                'checkout_url' => $data['attributes']['next_action']['redirect']['url'] ?? null,
            ];
        }

        // Mock / Sandbox deterministic response for automated tests & local development
        $mockId = 'pi_' . substr(md5(uniqid('pm_', true)), 0, 24);
        $mockClientKey = $mockId . '_client_sec_' . substr(md5(uniqid('key_', true)), 0, 16);

        return [
            'gateway' => 'paymongo',
            'gateway_payment_id' => $mockId,
            'client_secret' => $mockClientKey,
            'status' => 'awaiting_payment_method',
            'amount_cents' => $amountCents,
            'currency' => strtoupper($currency),
            'checkout_url' => "https://pm.link/pay/{$mockId}",
        ];
    }

    public function refund(string $paymentId, int $amountCents, string $reason = 'requested_by_customer'): array
    {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => $amountCents,
                    'payment_id' => $paymentId,
                    'reason' => $reason,
                    'notes' => 'Medicon Appointment Cancellation Refund',
                ],
            ],
        ];

        if (app()->environment('production') && !str_starts_with($this->secretKey, 'sk_test_medicon_mock')) {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/refunds", $payload);

            if ($response->failed()) {
                Log::error('PayMongo Refund Error: ' . $response->body());
                throw new RuntimeException('PayMongo Refund Failed: ' . $response->json('errors.0.detail', 'Refund Error'));
            }

            $data = $response->json('data');
            return [
                'success' => true,
                'refund_id' => $data['id'],
                'status' => $data['attributes']['status'] ?? 'succeeded',
                'amount_cents' => $data['attributes']['amount'],
            ];
        }

        // Mock deterministic refund response
        return [
            'success' => true,
            'refund_id' => 'ref_' . substr(md5(uniqid('pm_ref_', true)), 0, 20),
            'status' => 'succeeded',
            'amount_cents' => $amountCents,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($signature)) {
            return false;
        }

        // Parse PayMongo signature header: "t=timestamp,te=test_signature,li=live_signature"
        $parts = [];
        foreach (explode(',', $signature) as $segment) {
            $kv = explode('=', trim($segment), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $timestamp = $parts['t'] ?? null;
        $expectedSig = $parts['te'] ?? ($parts['li'] ?? null);

        if (!$timestamp || !$expectedSig) {
            // If raw signature string without prefix, verify direct HMAC
            $computed = hash_hmac('sha256', $payload, $this->webhookSecret);
            return hash_equals($computed, $signature);
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $computed = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($computed, $expectedSig);
    }
}
