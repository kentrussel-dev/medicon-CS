<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->isPatient() && $user->patient?->id === $patient->id) {
            return true;
        }

        if ($user->isDoctor()) {
            return true; // Doctors can view patient demographic/clinical profile for visits
        }

        return false;
    }

    public function update(User $user, Patient $patient): bool
    {
        return ($user->isPatient() && $user->patient?->id === $patient->id);
    }
}
