<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The devices entered before H29 were never approved by their shipping line, so the available
     * ones become inactive until bodega registers their approval.
     */
    public function up(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->foreignId('security_device_lot_approval_id')->nullable()->after('status')->constrained();
        });

        DB::table('security_devices')->where('status', 'available')->update(['status' => 'inactive']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('security_devices')->where('status', 'inactive')->update(['status' => 'available']);

        Schema::table('security_devices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('security_device_lot_approval_id');
        });
    }
};
