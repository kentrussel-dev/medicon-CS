<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;

class DoctorPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Doctor $doctor): bool
    {
        return true;
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return ($user->isDoctor() && $user->doctor?->id === $doctor->id);
    }
}
