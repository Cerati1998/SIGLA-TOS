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
        Schema::create('security_device_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_device_announcement_id')->unique()->constrained();
            $table->unsignedSmallInteger('quantity');
            $table->date('received_at');
            $table->string('dispatch_number', 50);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_device_lots');
    }
};
