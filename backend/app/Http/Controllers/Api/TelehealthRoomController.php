<?php

namespace App\Http\Controllers\Api;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentParticipant;
use App\Models\TelehealthMessage;
use App\Models\TelehealthRoom;
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
     * Create an instant ad-hoc consultation room with a unique code like `sdf-sdyy-125`.
     */
    public function createInstantRoom(Request $request): JsonResponse
    {
        $user = $request->user();
        $code = TelehealthRoom::generateUniqueCode();

        $room = TelehealthRoom::create([
            'room_code' => $code,
            'created_by' => $user->id,
            'title' => $request->input('title', 'Instant Clinical Consultation'),
            'status' => 'ACTIVE',
        ]);

        return response()->json([
            'success' => true,
            'room' => $room,
            'join_url' => url("/telehealth/room/{$code}"),
        ], 201);
    }

    /**
     * Issue a short-lived LiveKit Access Token for an authorized user (by appointment ID or room code).
     */
    public function getToken(Request $request, string $identifier): JsonResponse
    {
        if (is_numeric($identifier)) {
            $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail((int) $identifier);
        } else {
            $appointment = Appointment::with(['patient.user', 'doctor.user'])->where('room_code', $identifier)->first();
            if (!$appointment) {
                // Check if it's a standalone TelehealthRoom
                $room = TelehealthRoom::where('room_code', $identifier)->first();
                if ($room && $room->appointment_id) {
                    $appointment = Appointment::with(['patient.user', 'doctor.user'])->findOrFail($room->appointment_id);
                }
            }
        }

        if (!$appointment) {
            // Standalone ad-hoc room
            $room = TelehealthRoom::where('room_code', $identifier)->firstOrFail();
            if ($room->status === 'CLOSED') {
                return response()->json([
                    'success' => false,
                    'error' => 'This consultation room has ended and its data has been purged.',
                    'is_closed' => true,
                ], 410);
            }

            $user = $request->user();
            return response()->json([
                'success' => true,
                'room' => $room,
                'session' => [
                    'token' => "mock_token_{$room->room_code}_{$user->id}",
                    'room_name' => "room_{$room->room_code}",
                    'livekit_url' => config('services.livekit.url', 'ws://localhost:7880'),
                    'identity' => "user_{$user->id}",
                    'participant_name' => $user->name,
                    'role' => strtoupper($user->role?->value ?? 'patient'),
                ],
            ]);
        }

        $this->authorize('joinTelehealth', $appointment);

        // Ensure appointment has an active unique room code
        if (!$appointment->room_code) {
            $appointment->room_code = TelehealthRoom::generateUniqueCode();
            $appointment->save();
        }

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
                'room_code' => $appointment->room_code,
                'participant_identity' => $tokenData['identity'],
                'role' => $tokenData['role'],
            ],
            actor: $user
        );

        return response()->json([
            'success' => true,
            'room_code' => $appointment->room_code,
            'appointment' => [
                'id' => $appointment->id,
                'room_code' => $appointment->room_code,
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
     * Close consultation room and purge all in-room messages & tokens.
     */
    public function closeRoom(Request $request, string $identifier): JsonResponse
    {
        $appointment = is_numeric($identifier)
            ? Appointment::find((int) $identifier)
            : Appointment::where('room_code', $identifier)->first();

        if ($appointment) {
            $this->authorize('joinTelehealth', $appointment);

            // 1. Purge all in-call ephemeral messages
            TelehealthMessage::where('appointment_id', $appointment->id)->delete();

            // 2. Generate a fresh new room code for any future consultation
            $oldCode = $appointment->room_code;
            $appointment->room_code = TelehealthRoom::generateUniqueCode();
            $appointment->save();

            // 3. Mark any attached TelehealthRoom as CLOSED
            TelehealthRoom::where('appointment_id', $appointment->id)
                ->orWhere('room_code', $oldCode)
                ->update(['status' => 'CLOSED', 'closed_at' => now()]);

            $this->auditLogger->log(
                action: AuditAction::TELEHEALTH_LEAVE,
                recordType: 'Appointment',
                recordId: $appointment->id,
                patientId: $appointment->patient_id,
                newValues: ['action' => 'ROOM_CLOSED_AND_PURGED', 'old_room_code' => $oldCode],
                actor: $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Consultation room closed and all in-call messages securely purged.',
                'new_room_code' => $appointment->room_code,
            ]);
        }

        $room = TelehealthRoom::where('room_code', $identifier)->firstOrFail();
        $room->closeAndPurge();

        return response()->json([
            'success' => true,
            'message' => 'Ad-hoc consultation room closed and purged.',
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
