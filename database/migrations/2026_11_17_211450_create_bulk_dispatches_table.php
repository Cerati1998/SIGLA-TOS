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
        Schema::create('bulk_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiver_id')->constrained('workers');
            $table->foreignId('created_by')->constrained('users');
            $table->string('reason', 20);
            $table->foreignId('client_id')->nullable()->constrained();
            $table->foreignId('kit_id')->nullable()->constrained();
            $table->foreignId('yard_zone_id')->constrained();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_dispatches');
    }
};
