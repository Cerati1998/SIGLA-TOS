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
        Schema::create('certification_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_step_id')->constrained();
            $table->string('path', 255);
            $table->string('thumbnail_path', 255);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index(['certification_step_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_photos');
    }
};
