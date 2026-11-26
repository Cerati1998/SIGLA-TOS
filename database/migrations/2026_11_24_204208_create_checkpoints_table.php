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
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->unique()->constrained();
            $table->string('container_photo_path', 255)->nullable();
            $table->string('container_thumbnail_path', 255)->nullable();
            $table->foreignId('container_photo_by')->nullable()->constrained('users');
            $table->timestamp('container_photo_at')->nullable();
            $table->text('observations')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};
