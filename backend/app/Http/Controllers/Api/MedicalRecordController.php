<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalRecord\StoreMedicalRecordRequest;
use App\Http\Requests\MedicalRecord\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Services\AuditLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function __construct(protected AuditLoggerService $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = MedicalRecord::with(['doctor.user', 'patient.user', 'attachments']);

        if ($user->isPatient()) {
            $query->where('patient_id', $user->patient?->id);
        } elseif ($user->isDoctor()) {
            if ($patientId = $request->input('patient_id')) {
                $query->where('patient_id', $patientId);
            } else {
                $query->where('doctor_id', $user->doctor?->id);
            }
        }

        $records = $query->orderByDesc('record_date')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => MedicalRecordResource::collection($records),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    public function store(StoreMedicalRecordRequest $request): JsonResponse
    {
        $user = $request->user();
        $doctor = $user->doctor;

        if (!$doctor && !$user->isAdmin()) {
            return response()->json(['message' => 'Doctor profile required.'], 403);
        }

        $validated = $request->validated();
        $doctorId = $doctor ? $doctor->id : ($request->input('doctor_id') ?? 1);

        $record = MedicalRecord::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $doctorId,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'record_date' => $validated['record_date'],
            'diagnosis' => $validated['diagnosis'],
            'clinical_notes' => $validated['clinical_notes'],
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'vital_signs' => $validated['vital_signs'] ?? null,
            'icd_10_codes' => $validated['icd_10_codes'] ?? null,
        ]);

        $this->auditLogger->logCreate($record, $record->patient_id);

        return response()->json([
            'message' => 'Medical record created successfully.',
            'record' => new MedicalRecordResource($record->load(['doctor.user', 'patient.user'])),
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $record = MedicalRecord::with(['doctor.user', 'patient.user', 'attachments'])->findOrFail($id);
        $this->authorize('view', $record);

        $this->auditLogger->logView($record, $record->patient_id);

        return response()->json([
            'record' => new MedicalRecordResource($record),
        ]);
    }

    public function update(int $id, UpdateMedicalRecordRequest $request): JsonResponse
    {
        $record = MedicalRecord::findOrFail($id);
        $this->authorize('update', $record);

        $validated = $request->validated();
        $original = $record->getAttributes();

        $record->update(array_filter($validated, fn ($v) => $v !== null));

        $this->auditLogger->logUpdate($record, $original, $record->patient_id);

        return response()->json([
            'message' => 'Medical record updated.',
            'record' => new MedicalRecordResource($record->fresh(['doctor.user', 'patient.user', 'attachments'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $record = MedicalRecord::findOrFail($id);
        $this->authorize('delete', $record);

        $this->auditLogger->logDelete($record, $record->patient_id);
        $record->delete();

        return response()->json([
            'message' => 'Medical record archived (soft-deleted).',
        ]);
    }
}
