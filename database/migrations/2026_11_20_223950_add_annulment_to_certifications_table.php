<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Who annulled the assignment of the kit, when, why and what was done with the kit (H32). A
     * kit goes in a single certification that is not annulled, so its id is no longer unique: a reused kit keeps the
     * reference of the annulled certification and is assigned to another one.
     */
    public function up(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->timestamp('annulled_at')->nullable()->after('closed_by');
            $table->foreignId('annulled_by')->nullable()->after('annulled_at')->constrained('users');
            $table->string('annulment_reason', 500)->nullable()->after('annulled_by');
            $table->string('kit_decision', 20)->nullable()->after('annulment_reason');
            $table->index('kit_id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropUnique(['kit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->unique('kit_id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropIndex(['kit_id']);
            $table->dropConstrainedForeignId('annulled_by');
            $table->dropColumn(['annulled_at', 'annulment_reason', 'kit_decision']);
        });
    }
};
