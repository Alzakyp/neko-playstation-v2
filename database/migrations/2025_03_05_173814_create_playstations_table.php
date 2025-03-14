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
        Schema::create('playstations', function (Blueprint $table) {
            $table->id();
            $table->string('ps_number', 20);
            $table->string('ps_type', 50);
            $table->enum('status', ['available', 'in_use', 'maintenance'])->default('available');
            $table->decimal('hourly_rate', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playstations');
    }
};
