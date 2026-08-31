<?php

namespace App\Policies;

use App\Models\DoctorAvailability;
use App\Models\User;

class DoctorAvailabilityPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function manage(User $user, DoctorAvailability $availability): bool
    {
        return ($user->isDoctor() && $user->doctor?->id === $availability->doctor_id);
    }
}
