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
        Schema::create('security_device_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained();
            $table->foreignId('certification_step_id')->constrained();
            $table->foreignId('removed_device_id')->constrained('security_devices');
            $table->foreignId('installed_device_id')->unique()->constrained('security_devices');
            $table->foreignId('bulk_dispatch_id')->constrained();
            $table->string('reason', 255);
            $table->foreignId('replaced_by')->constrained('users');
            $table->timestamps();

            $table->index(['certification_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_device_replacements');
    }
};
