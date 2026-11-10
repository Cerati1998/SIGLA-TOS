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
        Schema::create('security_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_device_lot_id')->constrained();
            $table->string('serial_number', 50)->unique();
            $table->string('status', 20)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_devices');
    }
};
