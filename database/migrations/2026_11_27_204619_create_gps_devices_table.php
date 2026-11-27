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
        Schema::create('gps_devices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->unsignedBigInteger('monitoring_asset_id')->nullable()->unique();
            $table->foreignId('gps_device_model_id')->constrained('gps_device_models');
            $table->string('ownership', 10);
            $table->foreignId('rental_provider_id')->nullable()->constrained('providers');
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('status', 20)->default('available');
            $table->string('notes', 500)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('thumbnail_path', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_devices');
    }
};
