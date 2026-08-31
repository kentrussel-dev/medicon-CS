<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::with(['user', 'availabilities'])->where('is_active', true);

        if ($specialty = $request->input('specialty')) {
            $query->where('specialty', 'like', "%{$specialty}%");
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhere('specialty', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        if ($day = $request->input('day_of_week')) {
            $query->whereHas('availabilities', function ($a) use ($day) {
                $a->where('day_of_week', $day)->where('is_active', true);
            });
        }

        $doctors = $query->orderByDesc('rating')->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => DoctorResource::collection($doctors),
            'meta' => [
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'total' => $doctors->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $doctor = Doctor::with(['user', 'availabilities'])->findOrFail($id);

        return response()->json([
            'doctor' => new DoctorResource($doctor),
        ]);
    }

    public function specialties(): JsonResponse
    {
        $specialties = Doctor::where('is_active', true)
            ->distinct()
            ->pluck('specialty');

        return response()->json([
            'specialties' => $specialties,
        ]);
    }
}
