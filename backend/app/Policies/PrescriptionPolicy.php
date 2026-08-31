<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Prescription $prescription): bool
    {
        if ($user->isPatient() && $user->patient?->id === $prescription->patient_id) {
            return true;
        }

        if ($user->isDoctor() && $user->doctor?->id === $prescription->doctor_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isDoctor() || $user->isAdmin();
    }

    public function update(User $user, Prescription $prescription): bool
    {
        return ($user->isDoctor() && $user->doctor?->id === $prescription->doctor_id);
    }

    public function delete(User $user, Prescription $prescription): bool
    {
        return $user->isAdmin();
    }
}
