<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. An incorrect series in a verification section marked this way does not ask for a reinspection:
     * the checkpoint is closed with a required observation (T88, team decision of 2026-11-27). The step of a checkpoint
     * copies the mark when it is born, as it copies what the section asks for. «Verificación de cinta de puerta» is
     * marked from the start, also in the steps of the checkpoints already born.
     */
    public function up(): void
    {
        Schema::table('certification_sections', function (Blueprint $table) {
            $table->boolean('skips_reinspection')->default(false)->after('checkpoint_kind');
        });

        Schema::table('checkpoint_steps', function (Blueprint $table) {
            $table->boolean('skips_reinspection')->default(false)->after('kind');
        });

        $doorTape = DB::table('certification_sections')
            ->where('name', 'Verificación de cinta de puerta')
            ->where('type', 'checkpoint')
            ->where('checkpoint_kind', 'verification')
            ->pluck('id');

        DB::table('certification_sections')->whereIn('id', $doorTape)->update(['skips_reinspection' => true]);
        DB::table('checkpoint_steps')->whereIn('certification_section_id', $doorTape)->update(['skips_reinspection' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkpoint_steps', function (Blueprint $table) {
            $table->dropColumn('skips_reinspection');
        });

        Schema::table('certification_sections', function (Blueprint $table) {
            $table->dropColumn('skips_reinspection');
        });
    }
};
