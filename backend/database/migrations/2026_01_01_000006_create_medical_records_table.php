<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->date('record_date')->index();
            
            // Sensitive Clinical Fields Encrypted at Rest
            $table->text('diagnosis'); // Encrypted string
            $table->longText('clinical_notes'); // Encrypted string
            $table->text('treatment_plan')->nullable(); // Encrypted string
            $table->text('vital_signs')->nullable(); // Encrypted array (BP, Heart Rate, Temp, SpO2, Weight)
            $table->json('icd_10_codes')->nullable(); // ICD-10 diagnostic codes

            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'record_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
