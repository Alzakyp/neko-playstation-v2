<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('order_id', 100)->unique(); // Midtrans order ID
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'expire'])->default('pending');
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable(); // Will be filled after payment
            $table->string('transaction_id', 100)->nullable(); // Midtrans transaction ID
            $table->json('payment_data')->nullable(); // Store payment details from callback
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
