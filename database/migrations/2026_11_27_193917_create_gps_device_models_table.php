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
        Schema::create('gps_device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('providers');
            $table->string('name', 100);
            $table->string('category', 30);
            $table->string('opening_type', 10);
            $table->string('monitoring_product_code', 20)->nullable();
            $table->string('monitoring_device_type_code', 20)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['brand_id', 'name']);
            $table->unique(['monitoring_product_code', 'monitoring_device_type_code'], 'gps_device_models_monitoring_codes_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_device_models');
    }
};
