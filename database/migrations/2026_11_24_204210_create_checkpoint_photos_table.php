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
        Schema::create('checkpoint_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkpoint_id')->constrained();
            $table->foreignId('checkpoint_step_id')->nullable()->constrained();
            $table->string('path', 255);
            $table->string('thumbnail_path', 255);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index(['checkpoint_id', 'checkpoint_step_id', 'created_at'], 'checkpoint_photos_step_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_photos');
    }
};
