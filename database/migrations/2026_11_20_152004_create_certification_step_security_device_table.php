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
        Schema::create('certification_step_security_device', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_step_id')->constrained(indexName: 'certification_step_device_step_foreign');
            $table->foreignId('security_device_id')->unique('certification_step_device_device_unique')->constrained(indexName: 'certification_step_device_device_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_step_security_device');
    }
};
