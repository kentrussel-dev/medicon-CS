<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->dateTime('scheduled_start')->index();
            $table->dateTime('scheduled_end')->index();
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, CONFIRMED, IN_PROGRESS, COMPLETED, CANCELLED, NO_SHOW
            $table->string('type', 20)->default('TELEHEALTH'); // TELEHEALTH, IN_PERSON
            $table->string('reason');
            $table->text('notes')->nullable(); // Encrypted at Model level
            
            // ML Prediction metrics
            $table->decimal('no_show_risk_score', 5, 4)->nullable()->index();
            $table->string('no_show_risk_level', 10)->nullable()->index(); // LOW, MEDIUM, HIGH
            $table->json('risk_factors')->nullable();
            
            $table->boolean('is_reminder_sent')->default(false)->index();
            $table->text('cancellation_reason')->nullable();
            $table->string('meeting_link')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Conflict detection and fast lookup indexes
            $table->index(['doctor_id', 'scheduled_start', 'scheduled_end', 'status'], 'idx_doctor_schedule');
            $table->index(['patient_id', 'scheduled_start', 'status'], 'idx_patient_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
