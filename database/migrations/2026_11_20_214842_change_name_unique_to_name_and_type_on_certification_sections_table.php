<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The name of a section is unique within its type: an internal and an external section can be
     * called the same (H11, criterion 2; T63).
     */
    public function up(): void
    {
        Schema::table('certification_sections', function (Blueprint $table) {
            $table->unique(['name', 'type']);
        });

        Schema::table('certification_sections', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_sections', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('certification_sections', function (Blueprint $table) {
            $table->dropUnique(['name', 'type']);
        });
    }
};
