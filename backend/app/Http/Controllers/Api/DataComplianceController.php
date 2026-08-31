<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\Compliance\AccountDeletionService;
use App\Services\Compliance\DataExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DataComplianceController extends Controller
{
    public function __construct(
        protected DataExportService $exportService,
        protected AccountDeletionService $deletionService,
        protected TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Download complete personal and clinical health data export (JSON)
     */
    public function export(Request $request): JsonResponse
    {
        $user = $request->user();
        $exportData = $this->exportService->exportUserData($user);

        return response()->json([
            'success' => true,
            'filename' => "medicon_health_export_{$user->id}_" . date('Ymd_His') . '.json',
            'data' => $exportData,
        ]);
    }

    /**
     * Request account deletion and personal PII anonymization
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'two_factor_code' => 'nullable|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // 1. Password verification
        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password. Account deletion aborted.',
            ], 422);
        }

        // 2. 2FA verification if enabled
        if ($user->hasTwoFactorEnabled()) {
            $code = $request->input('two_factor_code');
            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication code is required to confirm account deletion.',
                ], 422);
            }

            $validTotp = $this->twoFactorService->verifyKey($user->two_factor_secret, $code);
            $validRecovery = !$validTotp && $this->twoFactorService->verifyAndConsumeRecoveryCode($user, $code);

            if (!$validTotp && !$validRecovery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid two-factor code or recovery code.',
                ], 422);
            }
        }

        $result = $this->deletionService->deleteAndAnonymizeAccount(
            $user,
            $request->input('reason', 'user_requested_deletion')
        );

        return response()->json($result);
    }
}
