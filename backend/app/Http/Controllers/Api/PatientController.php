<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\MedicalRecordResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PrescriptionResource;
use App\Models\Patient;
use App\Services\AuditLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(protected AuditLoggerService $auditLogger) {}

    public function show(int $id, Request $request): JsonResponse
    {
        $patient = Patient::with('user')->findOrFail($id);
        $this->authorize('view', $patient);

        $this->auditLogger->logView($patient, $patient->id);

        return response()->json([
            'patient' => new PatientResource($patient),
        ]);
    }

    public function history(int $id, Request $request): JsonResponse
    {
        $patient = Patient::with(['user'])->findOrFail($id);
        $this->authorize('view', $patient);

        $appointments = $patient->appointments()
            ->with(['doctor.user'])
            ->orderByDesc('scheduled_start')
            ->get();

        $medicalRecords = $patient->medicalRecords()
            ->with(['doctor.user', 'attachments'])
            ->orderByDesc('record_date')
            ->get();

        $prescriptions = $patient->prescriptions()
            ->with(['doctor.user', 'items'])
            ->orderByDesc('created_at')
            ->get();

        $this->auditLogger->log(
            action: \App\Enums\AuditAction::VIEW,
            recordType: 'PatientHistory',
            recordId: $patient->id,
            patientId: $patient->id
        );

        return response()->json([
            'patient' => new PatientResource($patient),
            'appointments' => AppointmentResource::collection($appointments),
            'medical_records' => MedicalRecordResource::collection($medicalRecords),
            'prescriptions' => PrescriptionResource::collection($prescriptions),
        ]);
    }
}
