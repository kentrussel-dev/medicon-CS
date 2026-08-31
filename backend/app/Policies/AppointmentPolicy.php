<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
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

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isPatient() && $user->patient?->id === $appointment->patient_id) {
            return true;
        }

        if ($user->isDoctor() && $user->doctor?->id === $appointment->doctor_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isPatient() || $user->isAdmin();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isDoctor() && $user->doctor?->id === $appointment->doctor_id) {
            return true;
        }

        if ($user->isPatient() && $user->patient?->id === $appointment->patient_id) {
            return $appointment->isCancellable();
        }

        return false;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->isPatient() && $user->patient?->id === $appointment->patient_id) {
            return $appointment->isCancellable();
        }

        if ($user->isDoctor() && $user->doctor?->id === $appointment->doctor_id) {
            return !$appointment->status->isTerminal();
        }

        return false;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin();
    }

    public function joinTelehealth(User $user, Appointment $appointment): bool
    {
        // 1. Patient assigned to appointment
        if ($user->isPatient() && $user->patient?->id === $appointment->patient_id) {
            return true;
        }

        // 2. Doctor assigned to appointment
        if ($user->isDoctor() && $user->doctor?->id === $appointment->doctor_id) {
            return true;
        }

        // 3. Registered participant (specialist, translator) invited to this appointment
        if ($appointment->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    public function manageParticipants(User $user, Appointment $appointment): bool
    {
        // Only the attending physician or admin can invite extra participants
        if ($user->isDoctor() && $user->doctor?->id === $appointment->doctor_id) {
            return true;
        }

        return false;
    }
}
