<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialty')->index(); // Cardiology, Dermatology, Pediatrics, General Practice, Neurology, Psychiatry, etc.
            $table->string('license_number')->unique();
            $table->text('bio')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(50.00);
            $table->unsignedSmallInteger('years_of_experience')->default(5);
            $table->decimal('rating', 3, 2)->default(4.90)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
