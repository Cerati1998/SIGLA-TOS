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
        Schema::table('shipping_lines', function (Blueprint $table) {
            $table->string('dispatch_mode', 20)->default('random')->after('sigla1_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_lines', function (Blueprint $table) {
            $table->dropColumn('dispatch_mode');
        });
    }
};
