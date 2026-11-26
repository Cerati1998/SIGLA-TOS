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
        Schema::table('security_device_replacements', function (Blueprint $table) {
            $table->foreignId('reinspection_id')->nullable()->after('bulk_dispatch_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_device_replacements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reinspection_id');
        });
    }
};
