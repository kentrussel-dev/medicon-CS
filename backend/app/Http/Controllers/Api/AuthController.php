<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected AuditLoggerService $auditLogger) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $role = isset($validated['role']) ? UserRole::from($validated['role']) : UserRole::PATIENT;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'phone' => $validated['phone'] ?? null,
            'email_verified_at' => now(), // Instant verification for dev
            'is_active' => true,
        ]);

        if ($role === UserRole::PATIENT) {
            Patient::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? 'F',
                'blood_type' => $validated['blood_type'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]);
        } elseif ($role === UserRole::DOCTOR) {
            Doctor::create([
                'user_id' => $user->id,
                'specialty' => $validated['specialty'] ?? 'General Practice',
                'license_number' => $validated['license_number'] ?? 'LIC-' . strtoupper(bin2hex(random_bytes(4))),
                'consultation_fee' => $validated['consultation_fee'] ?? 120.00,
                'consultation_fee_cents' => isset($validated['consultation_fee']) ? (int)round($validated['consultation_fee'] * 100) : 12000,
                'is_active' => true,
            ]);
        }

        $token = $user->createToken('medicon_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user->load(['patient', 'doctor'])),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account is deactivated. Please contact an administrator.',
                'status' => 403,
            ], 403);
        }

        // Two-Factor Authentication Enforcement
        if ($user->hasTwoFactorEnabled()) {
            $twoFactorToken = base64_encode(json_encode([
                'user_id' => $user->id,
                'email' => $user->email,
                'expires_at' => time() + 300, // 5 minute challenge window
            ]));

            return response()->json([
                'success' => true,
                'two_factor_required' => true,
                'two_factor_token' => $twoFactorToken,
                'message' => 'Two-factor authentication required. Please enter your 6-digit code or recovery code.',
            ]);
        }

        $deviceName = $validated['device_name'] ?? 'web_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user->load(['patient', 'doctor'])),
            'token' => $token,
            'two_factor_required' => false,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['patient', 'doctor.availabilities']);

        return response()->json([
            'user' => new UserResource($user),
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
        ]);
    }
}
