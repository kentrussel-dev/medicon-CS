<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('action', 30)->index(); // VIEW, CREATE, UPDATE, DELETE, EXPORT, DOWNLOAD
            $table->string('record_type', 100)->index(); // MedicalRecord, Prescription, Patient, Appointment
            $table->unsignedBigInteger('record_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['patient_id', 'created_at'], 'idx_audit_patient_time');
            $table->index(['user_id', 'created_at'], 'idx_audit_user_time');
            $table->index(['record_type', 'record_id'], 'idx_audit_record');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
