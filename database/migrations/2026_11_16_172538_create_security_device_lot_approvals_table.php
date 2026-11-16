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
        Schema::create('security_device_lot_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_device_lot_id')->constrained();
            $table->unsignedBigInteger('correlative_start');
            $table->unsignedBigInteger('correlative_end');
            $table->unsignedSmallInteger('approved_count');
            $table->foreignId('approved_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_device_lot_approvals');
    }
};
