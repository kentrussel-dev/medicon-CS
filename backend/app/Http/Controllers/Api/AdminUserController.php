<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function __construct(protected AuditLoggerService $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::with(['patient', 'doctor']);

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderByDesc('created_at')->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', new Enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => ['required_if:role,doctor', 'nullable', 'string', 'max:100'],
            'license_number' => ['required_if:role,doctor', 'nullable', 'string', 'max:50', 'unique:doctors,license_number'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $role = UserRole::from($validated['role']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        if ($role === UserRole::PATIENT) {
            Patient::create(['user_id' => $user->id]);
        } elseif ($role === UserRole::DOCTOR) {
            Doctor::create([
                'user_id' => $user->id,
                'specialty' => $validated['specialty'] ?? 'General Practice',
                'license_number' => $validated['license_number'] ?? 'LIC-' . strtoupper(bin2hex(random_bytes(4))),
                'consultation_fee' => $validated['consultation_fee'] ?? 50.00,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'message' => 'User created successfully.',
            'user' => new UserResource($user->load(['patient', 'doctor'])),
        ], 201);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot deactivate your own administrative account.'], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        if ($user->isDoctor() && $user->doctor) {
            $user->doctor->update(['is_active' => $user->is_active]);
        }

        return response()->json([
            'message' => 'User account status updated.',
            'user' => new UserResource($user->fresh(['patient', 'doctor'])),
        ]);
    }
}
