<?php

namespace App\Http\Controllers\Api;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentParticipant;
use App\Services\AuditLoggerService;
use App\Services\LiveKitTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelehealthRoomController extends Controller
{
    public function __construct(
        protected LiveKitTokenService $tokenService,
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Issue a short-lived LiveKit Access Token for an authorized user.
     */
    public function getToken(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($id);

        $this->authorize('joinTelehealth', $appointment);

        $user = $request->user();
        $tokenData = $this->tokenService->generateAppointmentToken($appointment, $user);

        // Record HIPAA audit event
        $this->auditLogger->log(
            action: AuditAction::TELEHEALTH_JOIN,
            recordType: 'Appointment',
            recordId: $appointment->id,
            patientId: $appointment->patient_id,
            newValues: [
                'room_name' => $tokenData['room_name'],
                'participant_identity' => $tokenData['identity'],
                'role' => $tokenData['role'],
            ],
            actor: $user
        );

        return response()->json([
            'success' => true,
            'appointment' => [
                'id' => $appointment->id,
                'patient_name' => $appointment->patient?->user?->name,
                'doctor_name' => $appointment->doctor?->user?->name,
                'doctor_specialty' => $appointment->doctor?->specialty,
                'scheduled_start' => $appointment->scheduled_start?->toIso8601String(),
                'type' => $appointment->type?->value,
                'status' => $appointment->status?->value,
            ],
            'session' => $tokenData,
        ]);
    }

    /**
     * List extra invited participants for this appointment.
     */
    public function getParticipants(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('joinTelehealth', $appointment);

        $participants = $appointment->participants()->latest()->get();

        return response()->json([
            'success' => true,
            'participants' => $participants,
        ]);
    }

    /**
     * Add an invited specialist or translator participant to an active consultation.
     */
    public function addParticipant(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('manageParticipants', $appointment);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'role' => ['required', 'string', 'in:specialist,translator,family,resident,observer'],
            'email' => ['nullable', 'email', 'max:150'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $participant = $appointment->participants()->create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
        ]);

        $tokenData = $this->tokenService->generateParticipantToken($appointment, $participant);

        // Record HIPAA audit event
        $this->auditLogger->log(
            action: AuditAction::TELEHEALTH_PARTICIPANT_ADDED,
            recordType: 'AppointmentParticipant',
            recordId: $participant->id,
            patientId: $appointment->patient_id,
            newValues: [
                'appointment_id' => $appointment->id,
                'participant_name' => $participant->name,
                'participant_role' => $participant->role,
            ],
            actor: $request->user()
        );

        return response()->json([
            'success' => true,
            'participant' => $participant,
            'session' => $tokenData,
        ], 201);
    }

    /**
     * Log telemetry / disconnect / leave events to HIPAA audit trail.
     */
    public function logEvent(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('joinTelehealth', $appointment);

        $validated = $request->validate([
            'event' => ['required', 'string', 'in:LEAVE,RECONNECT,STREAM_ERROR,CALL_ENDED'],
            'duration_seconds' => ['nullable', 'integer'],
        ]);

        $this->auditLogger->log(
            action: AuditAction::TELEHEALTH_LEAVE,
            recordType: 'Appointment',
            recordId: $appointment->id,
            patientId: $appointment->patient_id,
            newValues: [
                'event_type' => $validated['event'],
                'duration_seconds' => $validated['duration_seconds'] ?? null,
            ],
            actor: $request->user()
        );

        return response()->json(['success' => true]);
    }

    /**
     * Get in-room messages for an active consultation room.
     */
    public function getMessages(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('joinTelehealth', $appointment);

        $messages = \App\Models\TelehealthMessage::where('appointment_id', $appointment->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Broadcast / save a message inside the consultation room.
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('joinTelehealth', $appointment);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $message = \App\Models\TelehealthMessage::create([
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'sender_name' => $user->name,
            'sender_role' => strtoupper($user->role?->value ?? 'patient'),
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ], 201);
    }
}
