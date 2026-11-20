<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The types that already exist keep using a series and requiring the approval of their lots
     * (T62).
     */
    public function up(): void
    {
        Schema::table('security_device_types', function (Blueprint $table) {
            $table->boolean('uses_series')->default(true)->after('splits_internal_external');
            $table->boolean('requires_lot_approval')->default(true)->after('uses_series');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_device_types', function (Blueprint $table) {
            $table->dropColumn(['uses_series', 'requires_lot_approval']);
        });
    }
};
