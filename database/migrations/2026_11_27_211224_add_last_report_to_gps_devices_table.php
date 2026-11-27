<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The last report of the device in 24HM and when it was checked (H42).
     */
    public function up(): void
    {
        Schema::table('gps_devices', function (Blueprint $table) {
            $table->timestamp('last_reported_at')->nullable()->after('thumbnail_path');
            $table->decimal('last_latitude', 10, 7)->nullable()->after('last_reported_at');
            $table->decimal('last_longitude', 10, 7)->nullable()->after('last_latitude');
            $table->string('last_address', 255)->nullable()->after('last_longitude');
            $table->string('service_status', 10)->nullable()->after('last_address');
            $table->string('operational_status', 10)->nullable()->after('service_status');
            $table->decimal('supply_voltage', 5, 2)->nullable()->after('operational_status');
            $table->timestamp('last_checked_at')->nullable()->after('supply_voltage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_devices', function (Blueprint $table) {
            $table->dropColumn(['last_reported_at', 'last_latitude', 'last_longitude', 'last_address', 'service_status', 'operational_status', 'supply_voltage', 'last_checked_at']);
        });
    }
};
