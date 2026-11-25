<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Where the gate out request of the certification comes from (T81): `api` for SIGLA 1.0 and
     * `web` for the requests of SIGLA 2.0. The certifications that already exist came from SIGLA 1.0. The reference is
     * unique only within its source, so the same number in both systems does not collide.
     */
    public function up(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->string('source', 10)->default('api')->after('id');
            $table->unique(['source', 'gate_out_request_reference']);
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropUnique(['gate_out_request_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->unique('gate_out_request_reference');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropUnique(['source', 'gate_out_request_reference']);
            $table->dropColumn('source');
        });
    }
};
