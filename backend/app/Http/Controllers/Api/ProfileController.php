<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuditLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(protected AuditLoggerService $auditLogger) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['patient', 'doctor.availabilities']);
        return response()->json(['user' => new UserResource($user)]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $original = $user->getAttributes();

        $user->fill(array_filter([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
        ], fn ($val) => $val !== null));
        $user->save();

        if ($user->isPatient() && $user->patient) {
            $patientOriginal = $user->patient->getAttributes();
            $user->patient->fill(array_filter([
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'blood_type' => $validated['blood_type'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'medical_notes' => $validated['medical_notes'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'insurance_provider' => $validated['insurance_provider'] ?? null,
                'insurance_policy_number' => $validated['insurance_policy_number'] ?? null,
                'scholarship' => $validated['scholarship'] ?? null,
                'hypertension' => $validated['hypertension'] ?? null,
                'diabetes' => $validated['diabetes'] ?? null,
                'alcoholism' => $validated['alcoholism'] ?? null,
                'handicap_level' => $validated['handicap_level'] ?? null,
            ], fn ($val) => $val !== null));
            $user->patient->save();
            $this->auditLogger->logUpdate($user->patient, $patientOriginal, $user->patient->id);
        } elseif ($user->isDoctor() && $user->doctor) {
            $user->doctor->fill(array_filter([
                'specialty' => $validated['specialty'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'consultation_fee' => $validated['consultation_fee'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
            ], fn ($val) => $val !== null));
            $user->doctor->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user->fresh(['patient', 'doctor.availabilities'])),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()->symbols(), 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Current password does not match.',
                'errors' => ['current_password' => ['Incorrect current password.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
