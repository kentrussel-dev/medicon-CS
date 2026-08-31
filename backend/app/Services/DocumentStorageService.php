<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentStorageService
{
    protected array $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/dicom',
        'application/octet-stream', // Some DICOM / medical viewers
    ];

    protected int $maxSizeBytes = 10485760; // 10 MB

    public function upload(
        UploadedFile $file,
        Model $attachable,
        User $uploader,
        string $directory = 'medical-records'
    ): Attachment {
        $mime = $file->getMimeType();
        $size = $file->getSize();

        if (!in_array($mime, $this->allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                'file' => ['Invalid file format. Allowed formats: PDF, JPEG, PNG, and DICOM.'],
            ]);
        }

        if ($size > $this->maxSizeBytes) {
            throw ValidationException::withMessages([
                'file' => ['File size exceeds the 10MB compliance limit.'],
            ]);
        }

        $extension = $file->getClientOriginalExtension();
        $safeName = Str::uuid() . '.' . $extension;
        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';

        $filePath = $file->storeAs("attachments/{$directory}", $safeName, $disk);

        return Attachment::create([
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->getKey(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $size,
            'mime_type' => $mime,
            'uploaded_by_user_id' => $uploader->id,
        ]);
    }

    public function getTemporarySignedUrl(Attachment $attachment, int $expirationMinutes = 15): string
    {
        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';

        try {
            if ($disk === 's3' && Storage::disk('s3')->exists($attachment->file_path)) {
                return Storage::disk('s3')->temporaryUrl(
                    $attachment->file_path,
                    now()->addMinutes($expirationMinutes)
                );
            }
        } catch (\Exception $e) {
            // S3 temporaryUrl fallback
        }

        // Return signed route for local/private storage
        return url()->temporarySignedRoute(
            'api.attachments.download',
            now()->addMinutes($expirationMinutes),
            ['attachment' => $attachment->id]
        );
    }

    public function delete(Attachment $attachment): bool
    {
        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';
        if (Storage::disk($disk)->exists($attachment->file_path)) {
            Storage::disk($disk)->delete($attachment->file_path);
        }
        return $attachment->delete();
    }
}
