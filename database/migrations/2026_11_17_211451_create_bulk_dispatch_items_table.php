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
        Schema::create('bulk_dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_dispatch_id')->constrained();
            $table->foreignId('security_device_id')->unique()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_dispatch_items');
    }
};
