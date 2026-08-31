<?php

namespace App\Http\Resources;

use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(DocumentStorageService::class);
        $signedUrl = $storage->getTemporarySignedUrl($this->resource, 30);

        return [
            'id' => $this->id,
            'attachable_type' => class_basename($this->attachable_type),
            'attachable_id' => $this->attachable_id,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'file_size_formatted' => round($this->file_size / 1024, 1) . ' KB',
            'mime_type' => $this->mime_type,
            'download_url' => $signedUrl,
            'uploaded_by' => $this->uploader?->name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
