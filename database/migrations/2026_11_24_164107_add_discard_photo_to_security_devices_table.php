<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. The photo of the discard and its thumbnail, on the private disk (T77). The devices of a kit
     * disabled when its assignment is annulled share the photo of the kit (H32).
     */
    public function up(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->string('discard_photo_path', 255)->nullable()->after('discard_reason');
            $table->string('discard_thumbnail_path', 255)->nullable()->after('discard_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_devices', function (Blueprint $table) {
            $table->dropColumn(['discard_photo_path', 'discard_thumbnail_path']);
        });
    }
};
