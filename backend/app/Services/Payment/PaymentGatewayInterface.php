<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Get gateway identifier ('paymongo', 'stripe')
     */
    public function getName(): string;

    /**
     * Create a payment intent or checkout source
     *
     * @param int $amountCents Amount in integer centavos (e.g. 12000 for PHP 120.00)
     * @param string $currency 3-letter currency code (e.g. 'PHP')
     * @param string $paymentMethod 'gcash', 'grab_pay', 'paymaya', 'card'
     * @param array $metadata Extra metadata (appointment_id, user_id, patient_name)
     * @return array Standardized payment intent response
     */
    public function createPaymentIntent(int $amountCents, string $currency, string $paymentMethod, array $metadata = []): array;

    /**
     * Process a refund for a previously paid transaction
     *
     * @param string $paymentId Gateway transaction or payment intent ID
     * @param int $amountCents Refund amount in centavos
     * @param string $reason Cancellation / clinical refund reason
     * @return array Standardized refund response
     */
    public function refund(string $paymentId, int $amountCents, string $reason = 'requested_by_customer'): array;

    /**
     * Verify authenticity of incoming webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;
}
