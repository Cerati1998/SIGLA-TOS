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
        Schema::create('security_device_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_line_id')->constrained();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('security_device_type_id')->constrained();
            $table->string('series_prefix', 20);
            $table->unsignedTinyInteger('min_digits');
            $table->unsignedBigInteger('series_start');
            $table->unsignedBigInteger('series_end');
            $table->string('series_suffix', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_device_announcements');
    }
};
