<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Who discarded a device, when and why (T65).
     */
    public function up(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->timestamp('discarded_at')->nullable()->after('security_device_lot_approval_id');
            $table->foreignId('discarded_by')->nullable()->after('discarded_at')->constrained('users');
            $table->string('discard_reason', 255)->nullable()->after('discarded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discarded_by');
            $table->dropColumn(['discarded_at', 'discard_reason']);
        });
    }
};
