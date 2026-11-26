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
        Schema::table('certification_photos', function (Blueprint $table) {
            $table->foreignId('superseded_by_replacement_id')->nullable()->after('uploaded_by')->constrained('security_device_replacements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_photos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('superseded_by_replacement_id');
        });
    }
};
