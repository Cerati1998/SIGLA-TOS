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
        Schema::create('certification_reopenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_id')->constrained();
            $table->string('reason', 500);
            $table->foreignId('reopened_by')->constrained('users');
            $table->dateTime('closed_at');
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamps();

            $table->index(['certification_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_reopenings');
    }
};
