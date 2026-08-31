<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
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

    public function view(User $user, MedicalRecord $record): bool
    {
        if ($user->isPatient() && $user->patient?->id === $record->patient_id) {
            return true;
        }

        if ($user->isDoctor() && $user->doctor?->id === $record->doctor_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isDoctor() || $user->isAdmin();
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        if ($user->isDoctor() && $user->doctor?->id === $record->doctor_id) {
            return true;
        }
        return false;
    }

    public function delete(User $user, MedicalRecord $record): bool
    {
        return $user->isAdmin();
    }
}
