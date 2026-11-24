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
            $table->string('sigla1_name', 100)->nullable()->unique()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_lines', function (Blueprint $table) {
            $table->dropUnique(['sigla1_name']);
            $table->dropColumn('sigla1_name');
        });
    }
};
