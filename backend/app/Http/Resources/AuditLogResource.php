<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? 'System',
            'user_role' => $this->user?->role?->value,
            'patient_id' => $this->patient_id,
            'patient_name' => $this->patient?->user?->name,
            'action' => $this->action->value,
            'record_type' => $this->record_type,
            'record_id' => $this->record_id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
