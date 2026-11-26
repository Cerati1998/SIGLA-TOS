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
        Schema::table('checkpoint_marks', function (Blueprint $table) {
            $table->foreignId('reinspection_id')->nullable()->after('security_device_id')->constrained();
            $table->foreignId('superseded_by_reinspection_id')->nullable()->after('reinspection_id')->constrained('reinspections');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkpoint_marks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('superseded_by_reinspection_id');
            $table->dropConstrainedForeignId('reinspection_id');
        });
    }
};
