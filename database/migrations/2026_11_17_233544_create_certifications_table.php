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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->string('gate_out_request_reference', 50)->unique();
            $table->string('booking', 50);
            $table->string('container', 50);
            $table->foreignId('shipping_line_id')->constrained();
            $table->foreignId('carrier_id')->nullable()->constrained();
            $table->foreignId('driver_id')->nullable()->constrained();
            $table->foreignId('tractor_id')->nullable()->constrained('vehicles');
            $table->foreignId('semi_trailer_id')->nullable()->constrained('vehicles');
            $table->foreignId('kit_id')->unique()->constrained();
            $table->string('inspection_sheet_code', 50)->nullable()->unique();
            $table->foreignId('yard_zone_id')->constrained();
            $table->foreignId('certified_by')->constrained('users');
            $table->dateTime('started_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
