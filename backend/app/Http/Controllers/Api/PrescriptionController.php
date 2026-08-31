<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prescription\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\AuditLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function __construct(protected AuditLoggerService $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Prescription::with(['doctor.user', 'patient.user', 'items']);

        if ($user->isPatient()) {
            $query->where('patient_id', $user->patient?->id);
        } elseif ($user->isDoctor()) {
            if ($patientId = $request->input('patient_id')) {
                $query->where('patient_id', $patientId);
            } else {
                $query->where('doctor_id', $user->doctor?->id);
            }
        }

        $prescriptions = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => PrescriptionResource::collection($prescriptions),
            'meta' => [
                'current_page' => $prescriptions->currentPage(),
                'last_page' => $prescriptions->lastPage(),
                'total' => $prescriptions->total(),
            ],
        ]);
    }

    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        $user = $request->user();
        $doctor = $user->doctor;

        if (!$doctor && !$user->isAdmin()) {
            return response()->json(['message' => 'Doctor profile required.'], 403);
        }

        $validated = $request->validated();
        $doctorId = $doctor ? $doctor->id : ($request->input('doctor_id') ?? 1);

        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctorId,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'valid_until' => $validated['valid_until'],
            'is_dispensed' => false,
        ]);

        foreach ($validated['items'] as $item) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medication_name' => $item['medication_name'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration_days' => $item['duration_days'],
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        $this->auditLogger->logCreate($prescription, $prescription->patient_id);

        return response()->json([
            'message' => 'Prescription created successfully.',
            'prescription' => new PrescriptionResource($prescription->load(['doctor.user', 'patient.user', 'items'])),
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $prescription = Prescription::with(['doctor.user', 'patient.user', 'items', 'attachments'])->findOrFail($id);
        $this->authorize('view', $prescription);

        $this->auditLogger->logView($prescription, $prescription->patient_id);

        return response()->json([
            'prescription' => new PrescriptionResource($prescription),
        ]);
    }

    public function markDispensed(int $id): JsonResponse
    {
        $prescription = Prescription::findOrFail($id);
        $this->authorize('update', $prescription);

        $original = $prescription->getAttributes();
        $prescription->update(['is_dispensed' => true]);

        $this->auditLogger->logUpdate($prescription, $original, $prescription->patient_id);

        return response()->json([
            'message' => 'Prescription marked as dispensed.',
            'prescription' => new PrescriptionResource($prescription->fresh(['doctor.user', 'patient.user', 'items'])),
        ]);
    }
}
