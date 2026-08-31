<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\UploadAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\AuditLoggerService;
use App\Services\DocumentStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(
        protected DocumentStorageService $storageService,
        protected AuditLoggerService $auditLogger
    ) {}

    public function store(UploadAttachmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $attachableClass = match ($validated['attachable_type']) {
            'MedicalRecord' => MedicalRecord::class,
            'Appointment' => Appointment::class,
            'Patient' => Patient::class,
        };

        $attachable = $attachableClass::findOrFail($validated['attachable_id']);

        $attachment = $this->storageService->upload(
            file: $request->file('file'),
            attachable: $attachable,
            uploader: $user,
            directory: strtolower($validated['attachable_type']) . 's'
        );

        $patientId = $attachable instanceof Patient ? $attachable->id : ($attachable->patient_id ?? null);
        $this->auditLogger->logCreate($attachment, $patientId);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'attachment' => new AttachmentResource($attachment),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $attachment = Attachment::findOrFail($id);
        $patientId = $attachment->attachable instanceof Patient ? $attachment->attachable->id : ($attachment->attachable->patient_id ?? null);

        $this->auditLogger->log(
            action: \App\Enums\AuditAction::DOWNLOAD,
            recordType: 'Attachment',
            recordId: $attachment->id,
            patientId: $patientId
        );

        return response()->json([
            'attachment' => new AttachmentResource($attachment),
        ]);
    }

    public function download(Request $request, int $id)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Signed download link is invalid or expired.'], 403);
        }

        $attachment = Attachment::findOrFail($id);
        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';

        if (!Storage::disk($disk)->exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found on storage.'], 404);
        }

        return Storage::disk($disk)->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(int $id): JsonResponse
    {
        $attachment = Attachment::findOrFail($id);
        $patientId = $attachment->attachable instanceof Patient ? $attachment->attachable->id : ($attachment->attachable->patient_id ?? null);

        $this->auditLogger->logDelete($attachment, $patientId);
        $this->storageService->delete($attachment);

        return response()->json([
            'message' => 'Attachment deleted.',
        ]);
    }
}
