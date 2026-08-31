<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected TwoFactorAuthService $twoFactorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twoFactorService = new TwoFactorAuthService();
    }

    public function test_user_can_enable_two_factor_and_receive_secret_and_recovery_codes(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Secret123!'),
            'role' => UserRole::PATIENT,
        ]);

        $response = $this->actingAs($user)->postJson('/api/auth/2fa/enable');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'secret',
                    'qr_code_uri',
                    'recovery_codes',
                ],
            ]);

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertCount(8, $user->fresh()->two_factor_recovery_codes);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_user_can_confirm_two_factor_with_valid_totp_code(): void
    {
        $user = User::factory()->create(['role' => UserRole::PATIENT]);
        $secret = $this->twoFactorService->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $this->twoFactorService->generateRecoveryCodes(),
        ])->save();

        // Calculate valid time slice code
        $timeSlice = (int) floor(time() / 30);
        $validCode = $this->twoFactorService->calculateCode($secret, $timeSlice);

        $response = $this->actingAs($user)->postJson('/api/auth/2fa/confirm', [
            'code' => $validCode,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['two_factor_enabled' => true],
            ]);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_cannot_confirm_two_factor_with_invalid_code(): void
    {
        $user = User::factory()->create(['role' => UserRole::PATIENT]);
        $user->forceFill([
            'two_factor_secret' => $this->twoFactorService->generateSecretKey(),
        ])->save();

        $response = $this->actingAs($user)->postJson('/api/auth/2fa/confirm', [
            'code' => '000000',
        ]);

        $response->assertStatus(422);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_login_enforces_two_factor_challenge_when_enabled(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $user = User::factory()->create([
            'email' => 'twofactor@medicon.health',
            'password' => Hash::make('SecurePassword123!'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'twofactor@medicon.health',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'two_factor_required' => true,
            ])
            ->assertJsonStructure([
                'two_factor_token',
            ]);
    }

    public function test_user_can_authenticate_2fa_challenge_with_totp_code(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $user = User::factory()->create([
            'email' => 'totp@medicon.health',
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $twoFactorToken = base64_encode(json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'expires_at' => time() + 300,
        ]));

        $validCode = $this->twoFactorService->calculateCode($secret, (int) floor(time() / 30));

        $response = $this->postJson('/api/auth/2fa/challenge', [
            'two_factor_token' => $twoFactorToken,
            'code' => $validCode,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'user',
            ]);
    }

    public function test_user_can_authenticate_2fa_challenge_with_single_use_recovery_code(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $recoveryCodes = ['ABCD-1234', 'EFGH-5678', 'JKLM-9012'];

        $user = User::factory()->create([
            'email' => 'recovery@medicon.health',
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        $twoFactorToken = base64_encode(json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'expires_at' => time() + 300,
        ]));

        // First redemption should succeed
        $response = $this->postJson('/api/auth/2fa/challenge', [
            'two_factor_token' => $twoFactorToken,
            'recovery_code' => 'ABCD-1234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'recovery_code_used' => true,
            ]);

        // Verify recovery code was consumed and removed from user record
        $freshUser = $user->fresh();
        $this->assertNotContains('ABCD-1234', $freshUser->two_factor_recovery_codes);
        $this->assertCount(2, $freshUser->two_factor_recovery_codes);

        // Attempting to reuse the same recovery code must fail
        $reuseResponse = $this->postJson('/api/auth/2fa/challenge', [
            'two_factor_token' => $twoFactorToken,
            'recovery_code' => 'ABCD-1234',
        ]);

        $reuseResponse->assertStatus(422);
    }
}
