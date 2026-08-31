<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'avatar_url' => $this->user?->avatar_url,
            'specialty' => $this->specialty,
            'license_number' => $this->license_number,
            'bio' => $this->bio,
            'consultation_fee' => (float) $this->consultation_fee,
            'years_of_experience' => $this->years_of_experience,
            'rating' => (float) $this->rating,
            'is_active' => $this->is_active,
            'availabilities' => DoctorAvailabilityResource::collection($this->whenLoaded('availabilities')),
        ];
    }
}
