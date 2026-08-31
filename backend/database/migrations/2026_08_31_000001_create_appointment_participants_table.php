<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 150);
            $table->string('role', 50)->default('specialist'); // specialist, translator, family, resident
            $table->string('email', 150)->nullable();
            $table->string('access_token_hash', 64)->nullable()->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_participants');
    }
};
