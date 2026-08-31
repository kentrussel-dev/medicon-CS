<?php

use App\Http\Controllers\Api\AdminAnalyticsController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorAvailabilityController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Medicon Healthcare Platform
|--------------------------------------------------------------------------
*/

// Health Check & Monitoring
Route::get('/health', [HealthCheckController::class, 'health']);

// Public Doctor Discovery
Route::get('/doctors/specialties', [DoctorController::class, 'specialties']);
Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);

// Signed File Download
Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
    ->name('api.attachments.download');

// Authentication Routes (Rate-limited)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected Authenticated Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
    });

    // Patient Clinical Records & History
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::get('/patients/{id}/history', [PatientController::class, 'history'])->middleware('throttle:records');

    // Doctor Schedule & Availability
    Route::get('/doctor-availabilities', [DoctorAvailabilityController::class, 'index']);
    Route::post('/doctor-availabilities', [DoctorAvailabilityController::class, 'store'])
        ->middleware('role:doctor,admin');

    // Appointment Scheduling
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('/{id}', [AppointmentController::class, 'show']);
        Route::post('/{id}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::post('/{id}/cancel', [AppointmentController::class, 'cancel']);
        Route::patch('/{id}/status', [AppointmentController::class, 'updateStatus']);
    });

    // Medical Records (Encrypted Clinical Data)
    Route::prefix('medical-records')->middleware('throttle:records')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'index']);
        Route::post('/', [MedicalRecordController::class, 'store'])->middleware('role:doctor,admin');
        Route::get('/{id}', [MedicalRecordController::class, 'show']);
        Route::put('/{id}', [MedicalRecordController::class, 'update'])->middleware('role:doctor,admin');
        Route::delete('/{id}', [MedicalRecordController::class, 'destroy'])->middleware('role:admin');
    });

    // Prescriptions
    Route::prefix('prescriptions')->group(function () {
        Route::get('/', [PrescriptionController::class, 'index']);
        Route::post('/', [PrescriptionController::class, 'store'])->middleware('role:doctor,admin');
        Route::get('/{id}', [PrescriptionController::class, 'show']);
        Route::patch('/{id}/dispense', [PrescriptionController::class, 'markDispensed']);
    });

    // Attachments & Lab Uploads
    Route::prefix('attachments')->group(function () {
        Route::post('/', [AttachmentController::class, 'store']);
        Route::get('/{id}', [AttachmentController::class, 'show']);
        Route::delete('/{id}', [AttachmentController::class, 'destroy']);
    });

    // Admin & Operational Endpoints
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Analytics
        Route::get('/analytics/dashboard', [AdminAnalyticsController::class, 'dashboard']);
        Route::get('/analytics/high-risk', [AdminAnalyticsController::class, 'highRiskAppointments']);

        // User Management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::patch('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);

        // Compliance & Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
    });
});
