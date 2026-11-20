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
        Schema::create('certification_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained();
            $table->foreignId('certification_section_id')->constrained();
            $table->string('type', 20);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['certification_id', 'certification_section_id'], 'certification_steps_section_unique');
            $table->index(['certification_id', 'type', 'position'], 'certification_steps_type_position_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_steps');
    }
};
