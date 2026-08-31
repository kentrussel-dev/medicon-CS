<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\Appointment\UpdateStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\AppointmentSchedulingService;
use App\Services\AuditLoggerService;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentSchedulingService $schedulingService,
        protected AuditLoggerService $auditLogger,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Appointment::with(['patient.user', 'doctor.user', 'medicalRecord', 'prescription']);

        if ($user->isPatient()) {
            $query->where('patient_id', $user->patient?->id);
        } elseif ($user->isDoctor()) {
            $query->where('doctor_id', $user->doctor?->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->input('start_date')) {
            $query->where('scheduled_start', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate = $request->input('end_date')) {
            $query->where('scheduled_end', '<=', Carbon::parse($endDate)->endOfDay());
        }

        if ($riskLevel = $request->input('risk_level')) {
            $query->where('no_show_risk_level', $riskLevel);
        }

        $appointments = $query->orderByDesc('scheduled_start')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => AppointmentResource::collection($appointments),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    public function store(BookAppointmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->isPatient()) {
            $patient = $user->patient;
            if (!$patient) {
                return response()->json(['message' => 'Patient profile required.'], 422);
            }
        } elseif ($user->isAdmin() && isset($validated['patient_id'])) {
            $patient = Patient::findOrFail($validated['patient_id']);
        } else {
            return response()->json(['message' => 'Cannot book appointment without a valid patient.'], 422);
        }

        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $type = isset($validated['type']) ? AppointmentType::from($validated['type']) : AppointmentType::TELEHEALTH;

        $appointment = $this->schedulingService->book(
            patient: $patient,
            doctor: $doctor,
            scheduledStart: $validated['scheduled_start'],
            scheduledEnd: $validated['scheduled_end'],
            reason: $validated['reason'],
            type: $type,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Appointment successfully scheduled.',
            'appointment' => new AppointmentResource($appointment),
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user', 'medicalRecord.attachments', 'prescription.items'])
            ->findOrFail($id);

        $this->authorize('view', $appointment);
        $this->auditLogger->logView($appointment, $appointment->patient_id);

        return response()->json([
            'appointment' => new AppointmentResource($appointment),
        ]);
    }

    public function reschedule(int $id, RescheduleAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::with(['doctor', 'patient'])->findOrFail($id);
        $this->authorize('update', $appointment);

        $validated = $request->validated();

        $updated = $this->schedulingService->reschedule(
            appointment: $appointment,
            newStart: $validated['scheduled_start'],
            newEnd: $validated['scheduled_end'],
            reason: $validated['reason'] ?? null
        );

        return response()->json([
            'message' => 'Appointment rescheduled successfully.',
            'appointment' => new AppointmentResource($updated),
        ]);
    }

    public function cancel(int $id, CancelAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('cancel', $appointment);

        $validated = $request->validated();

        $cancelled = $this->schedulingService->cancel(
            appointment: $appointment,
            cancellationReason: $validated['cancellation_reason']
        );

        $refundResult = $this->paymentService->processCancellationRefund($cancelled, $validated['cancellation_reason']);

        return response()->json([
            'message' => 'Appointment cancelled.',
            'appointment' => new AppointmentResource($cancelled),
            'refund' => $refundResult,
        ]);
    }

    public function updateStatus(int $id, UpdateStatusRequest $request): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('update', $appointment);

        $validated = $request->validated();
        $original = $appointment->getAttributes();

        $appointment->status = AppointmentStatus::from($validated['status']);
        if (isset($validated['notes'])) {
            $appointment->notes = $validated['notes'];
        }
        if (isset($validated['cancellation_reason'])) {
            $appointment->cancellation_reason = $validated['cancellation_reason'];
        }
        $appointment->save();

        $this->auditLogger->logUpdate($appointment, $original, $appointment->patient_id);

        return response()->json([
            'message' => 'Appointment status updated.',
            'appointment' => new AppointmentResource($appointment->fresh(['patient.user', 'doctor.user'])),
        ]);
    }
}
