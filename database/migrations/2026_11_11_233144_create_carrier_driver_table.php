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
        Schema::create('carrier_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained();
            $table->foreignId('driver_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['carrier_id', 'driver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_driver');
    }
};
