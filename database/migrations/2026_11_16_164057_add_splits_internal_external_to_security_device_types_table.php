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
        Schema::table('security_device_types', function (Blueprint $table) {
            $table->boolean('splits_internal_external')->default(false)->after('kind');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_device_types', function (Blueprint $table) {
            $table->dropColumn('splits_internal_external');
        });
    }
};
