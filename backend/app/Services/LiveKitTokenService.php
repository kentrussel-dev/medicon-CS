<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentParticipant;
use App\Models\User;

class LiveKitTokenService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $livekitUrl;
    protected string $publicUrl;
    protected int $tokenTtlMinutes;

    public function __construct()
    {
        $this->apiKey = (string) config('services.livekit.api_key', 'devkey');
        $this->apiSecret = (string) config('services.livekit.api_secret', 'secret');
        $this->livekitUrl = (string) config('services.livekit.url', 'http://livekit:7880');
        $this->publicUrl = (string) config('services.livekit.public_url', 'ws://localhost:7880');
        $this->tokenTtlMinutes = (int) config('services.livekit.token_ttl_minutes', 120);
    }

    /**
     * Generate an appointment-scoped LiveKit Access Token for an authenticated User.
     */
    public function generateAppointmentToken(Appointment $appointment, User $user, ?string $customRole = null): array
    {
        $roomName = $this->getRoomName($appointment);
        $role = $customRole ?? $user->role?->value ?? 'patient';
        $identity = "user_{$user->id}_{$role}";
        $name = $user->name;

        $metadata = [
            'user_id' => $user->id,
            'role' => strtoupper($role),
            'appointment_id' => $appointment->id,
            'is_host' => $user->isDoctor() || $user->isAdmin(),
        ];

        $token = $this->createJwtToken(
            identity: $identity,
            name: $name,
            roomName: $roomName,
            metadata: $metadata
        );

        return [
            'token' => $token,
            'room_name' => $roomName,
            'livekit_url' => $this->publicUrl,
            'identity' => $identity,
            'participant_name' => $name,
            'role' => strtoupper($role),
            'is_host' => $metadata['is_host'],
            'expires_at' => now()->addMinutes($this->tokenTtlMinutes)->toIso8601String(),
        ];
    }

    /**
     * Generate an access token for an invited external participant (specialist, translator, family).
     */
    public function generateParticipantToken(Appointment $appointment, AppointmentParticipant $participant): array
    {
        $roomName = $this->getRoomName($appointment);
        $identity = "participant_{$participant->id}_{$participant->role}";
        $name = $participant->name;

        $metadata = [
            'participant_id' => $participant->id,
            'role' => strtoupper($participant->role),
            'appointment_id' => $appointment->id,
            'is_host' => false,
        ];

        $token = $this->createJwtToken(
            identity: $identity,
            name: $name,
            roomName: $roomName,
            metadata: $metadata
        );

        return [
            'token' => $token,
            'room_name' => $roomName,
            'livekit_url' => $this->publicUrl,
            'identity' => $identity,
            'participant_name' => $name,
            'role' => strtoupper($participant->role),
            'is_host' => false,
            'expires_at' => now()->addMinutes($this->tokenTtlMinutes)->toIso8601String(),
        ];
    }

    /**
     * Creates a signed LiveKit AccessToken JWT using HMAC-SHA256.
     */
    public function createJwtToken(
        string $identity,
        string $name,
        string $roomName,
        array $metadata = [],
        array $videoGrants = []
    ): string {
        $now = time();
        $exp = $now + ($this->tokenTtlMinutes * 60);

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $defaultGrants = [
            'room' => $roomName,
            'roomJoin' => true,
            'canPublish' => true,
            'canSubscribe' => true,
            'canPublishData' => true,
        ];

        $payload = [
            'iss' => $this->apiKey,
            'sub' => $identity,
            'nbf' => $now - 10,
            'exp' => $exp,
            'name' => $name,
            'metadata' => json_encode($metadata),
            'video' => array_merge($defaultGrants, $videoGrants),
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->apiSecret, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

    /**
     * Formats deterministic room name per appointment.
     */
    public function getRoomName(Appointment $appointment): string
    {
        return "medicon_room_appt_{$appointment->id}";
    }

    /**
     * URL-safe Base64 encoder without padding.
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
