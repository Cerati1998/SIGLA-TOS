<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. A serial number is unique within its scope (T62): 0 for the types that use a series, so their
     * serial numbers are unique in all of SIGLA, or the id of a type that does not use one, so its serial numbers are
     * unique only within that type.
     */
    public function up(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('serial_number_scope')->default(0)->after('security_device_lot_id');
            $table->unique(['serial_number_scope', 'serial_number']);
        });

        Schema::table('security_devices', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
            $table->index('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->dropIndex(['serial_number']);
            $table->unique('serial_number');
        });

        Schema::table('security_devices', function (Blueprint $table) {
            $table->dropUnique(['serial_number_scope', 'serial_number']);
            $table->dropColumn('serial_number_scope');
        });
    }
};
