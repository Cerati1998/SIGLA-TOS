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
        Schema::create('kit_type_security_device_type', function (Blueprint $table) {
            $table->foreignId('kit_type_id')->constrained();
            $table->foreignId('security_device_type_id')->constrained();
            $table->unsignedSmallInteger('quantity');

            $table->primary(['kit_type_id', 'security_device_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kit_type_security_device_type');
    }
};
