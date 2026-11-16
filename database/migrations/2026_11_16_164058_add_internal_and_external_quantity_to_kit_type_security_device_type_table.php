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
        Schema::table('kit_type_security_device_type', function (Blueprint $table) {
            $table->unsignedSmallInteger('internal_quantity')->nullable()->after('quantity');
            $table->unsignedSmallInteger('external_quantity')->nullable()->after('internal_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kit_type_security_device_type', function (Blueprint $table) {
            $table->dropColumn(['internal_quantity', 'external_quantity']);
        });
    }
};
