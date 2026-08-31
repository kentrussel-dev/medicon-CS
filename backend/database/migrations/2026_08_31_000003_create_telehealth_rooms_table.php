<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telehealth_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code', 64)->unique()->index();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255)->default('Clinical Telehealth Consultation');
            $table->string('status', 32)->default('ACTIVE'); // ACTIVE, CLOSED, EXPIRED
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // Add room_code to appointments table if not present
        if (!Schema::hasColumn('appointments', 'room_code')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('room_code', 64)->nullable()->unique()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('telehealth_rooms');

        if (Schema::hasColumn('appointments', 'room_code')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('room_code');
            });
        }
    }
};
