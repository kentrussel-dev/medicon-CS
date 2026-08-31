<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date_of_birth')->nullable()->index();
            $table->string('gender', 10)->default('F')->index();
            $table->string('blood_type', 5)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('allergies')->nullable(); // Encrypted at Model level
            $table->text('medical_notes')->nullable(); // Encrypted at Model level
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            
            // Medical Profile for ML Risk modeling
            $table->boolean('scholarship')->default(false)->index();
            $table->boolean('hypertension')->default(false)->index();
            $table->boolean('diabetes')->default(false)->index();
            $table->boolean('alcoholism')->default(false)->index();
            $table->unsignedTinyInteger('handicap_level')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
