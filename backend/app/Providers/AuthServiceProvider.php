<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\DoctorAvailabilityPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\PatientPolicy;
use App\Policies\PrescriptionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Appointment::class => AppointmentPolicy::class,
        MedicalRecord::class => MedicalRecordPolicy::class,
        Prescription::class => PrescriptionPolicy::class,
        Patient::class => PatientPolicy::class,
        Doctor::class => DoctorPolicy::class,
        DoctorAvailability::class => DoctorAvailabilityPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gates for role checks
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('doctor', fn (User $user) => $user->isDoctor());
        Gate::define('patient', fn (User $user) => $user->isPatient());
    }
}
