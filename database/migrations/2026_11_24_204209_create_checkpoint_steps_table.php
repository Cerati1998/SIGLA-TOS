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
        Schema::create('checkpoint_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkpoint_id')->constrained();
            $table->foreignId('certification_section_id')->constrained();
            $table->string('kind', 20);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['checkpoint_id', 'certification_section_id'], 'checkpoint_steps_section_unique');
            $table->index(['checkpoint_id', 'position'], 'checkpoint_steps_position_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_steps');
    }
};
