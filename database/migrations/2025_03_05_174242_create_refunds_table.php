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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained();
            $table->foreignId('payment_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->integer('refund_percentage'); // 0, 50, 75, or 100
            $table->dateTime('request_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->foreignId('admin_id')->nullable()->constrained('users');
            $table->dateTime('processed_date')->nullable();
            $table->text('notes')->nullable();

            // Midtrans refund reference
            $table->string('refund_id', 100)->nullable(); // Midtrans refund ID
            $table->json('refund_response')->nullable(); // Store Midtrans refund response

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
