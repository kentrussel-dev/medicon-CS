<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_cents'); // Stored in centavos (e.g. 12000 = PHP 120.00)
            $table->string('currency', 3)->default('PHP');
            $table->string('gateway', 32); // 'paymongo', 'stripe'
            $table->string('payment_method', 32); // 'gcash', 'grab_pay', 'paymaya', 'card'
            $table->string('status', 32)->default('pending'); // 'pending', 'paid', 'failed', 'refunded'
            $table->string('gateway_payment_id')->nullable()->index();
            $table->string('gateway_client_secret')->nullable();
            $table->unsignedBigInteger('refund_amount_cents')->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->unsignedBigInteger('cancellation_fee_cents')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('unpaid')->after('status');
            $table->unsignedBigInteger('consultation_fee_cents')->default(12000)->after('payment_status');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedBigInteger('consultation_fee_cents')->default(12000)->after('consultation_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('consultation_fee_cents');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'consultation_fee_cents']);
        });

        Schema::dropIfExists('payments');
    }
};
