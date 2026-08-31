<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Step 1: Enable 2FA - Generate Secret Key & Recovery Codes
     */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        $secret = $this->twoFactorService->generateSecretKey();
        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
        $qrCodeUri = $this->twoFactorService->generateQrCodeUri(
            config('app.name', 'Medicon Healthcare'),
            $user->email,
            $secret
        );

        // Store pending secret & recovery codes until verified
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor secret generated. Please scan the QR code and confirm with a 6-digit code.',
            'data' => [
                'secret' => $secret,
                'qr_code_uri' => $qrCodeUri,
                'recovery_codes' => $recoveryCodes,
            ],
        ]);
    }

    /**
     * Step 2: Confirm 2FA Activation with a 6-digit TOTP code
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (empty($user->two_factor_secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication has not been initialized. Please enable it first.',
            ], 400);
        }

        if (!$this->twoFactorService->verifyKey($user->two_factor_secret, $request->input('code'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication code. Please check your authenticator app and try again.',
            ], 422);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication is now active on your account.',
            'data' => [
                'two_factor_enabled' => true,
                'confirmed_at' => $user->two_factor_confirmed_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Disable 2FA with password confirmation
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 422);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication has been disabled.',
            'data' => [
                'two_factor_enabled' => false,
            ],
        ]);
    }

    /**
     * Challenge verification during login (accepts 6-digit TOTP or 8-char recovery code)
     */
    public function challenge(Request $request): JsonResponse
    {
        $request->validate([
            'two_factor_token' => 'required|string',
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        // Decode temporary token
        $tokenData = json_decode(base64_decode($request->input('two_factor_token')), true);

        if (!$tokenData || empty($tokenData['user_id']) || empty($tokenData['expires_at']) || $tokenData['expires_at'] < time()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor session expired. Please sign in again.',
            ], 401);
        }

        $user = User::find($tokenData['user_id']);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid two-factor session.',
            ], 400);
        }

        $isValid = false;
        $isRecoveryUsed = false;

        // Try TOTP code
        if ($code = $request->input('code')) {
            $isValid = $this->twoFactorService->verifyKey($user->two_factor_secret, $code);
        }
        // Try Single-Use Recovery Code
        elseif ($recoveryCode = $request->input('recovery_code')) {
            $isValid = $this->twoFactorService->verifyAndConsumeRecoveryCode($user, $recoveryCode);
            $isRecoveryUsed = true;
        }

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication code or recovery code.',
            ], 422);
        }

        // Issue Sanctum Token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication verified successfully.',
            'user' => new UserResource($user),
            'token' => $token,
            'recovery_code_used' => $isRecoveryUsed,
            'remaining_recovery_codes_count' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }

    /**
     * Regenerate fresh recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not active.',
            ], 400);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'New recovery codes generated.',
            'data' => [
                'recovery_codes' => $recoveryCodes,
            ],
        ]);
    }
}
