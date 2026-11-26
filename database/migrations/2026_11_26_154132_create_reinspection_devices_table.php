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
        Schema::create('reinspection_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reinspection_id')->constrained();
            $table->foreignId('certification_step_id')->constrained();
            $table->foreignId('security_device_id')->constrained();
            $table->foreignId('security_device_replacement_id')->nullable()->unique()->constrained();
            $table->timestamps();

            $table->unique(['reinspection_id', 'security_device_id'], 'reinspection_devices_device_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reinspection_devices');
    }
};
