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
        Schema::create('checkpoint_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkpoint_step_id')->constrained();
            $table->foreignId('security_device_id')->constrained();
            $table->boolean('is_correct')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users');
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();

            $table->unique(['checkpoint_step_id', 'security_device_id'], 'checkpoint_marks_device_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_marks');
    }
};
