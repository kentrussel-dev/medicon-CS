<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\SetAvailabilityRequest;
use App\Http\Resources\DoctorAvailabilityResource;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = $user->doctor;

        if (!$doctor && !$user->isAdmin()) {
            return response()->json(['message' => 'Doctor profile required.'], 403);
        }

        $doctorId = $request->input('doctor_id', $doctor?->id);
        $availabilities = DoctorAvailability::where('doctor_id', $doctorId)->orderBy('day_of_week')->get();

        return response()->json([
            'availabilities' => DoctorAvailabilityResource::collection($availabilities),
        ]);
    }

    public function store(SetAvailabilityRequest $request): JsonResponse
    {
        $user = $request->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return response()->json(['message' => 'Doctor profile not found.'], 403);
        }

        $validated = $request->validated();

        // Transactionally update doctor availability
        DoctorAvailability::where('doctor_id', $doctor->id)->delete();

        $created = [];
        foreach ($validated['slots'] as $slot) {
            $created[] = DoctorAvailability::create([
                'doctor_id' => $doctor->id,
                'day_of_week' => $slot['day_of_week'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'slot_duration_minutes' => $slot['slot_duration_minutes'] ?? 30,
                'is_active' => $slot['is_active'] ?? true,
            ]);
        }

        return response()->json([
            'message' => 'Availability schedule successfully updated.',
            'availabilities' => DoctorAvailabilityResource::collection(collect($created)),
        ]);
    }
}
