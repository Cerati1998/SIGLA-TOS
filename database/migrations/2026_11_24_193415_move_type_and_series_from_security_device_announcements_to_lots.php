<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H37: an announcement of the entry of lots brings one or more lots. The reception (date, dispatch number and who entered
 * it) moves up to the announcement, and the type and the series move down to each lot. The data that exists is kept:
 * until now each announcement had a single lot.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('security_device_lots', function (Blueprint $table) {
            $table->foreignId('security_device_type_id')->nullable()->after('security_device_announcement_id')->constrained();
            $table->string('series_prefix', 20)->nullable()->after('security_device_type_id');
            $table->unsignedTinyInteger('min_digits')->nullable()->after('series_prefix');
            $table->unsignedBigInteger('series_start')->nullable()->after('min_digits');
            $table->unsignedBigInteger('series_end')->nullable()->after('series_start');
            $table->string('series_suffix', 10)->nullable()->after('series_end');
        });

        Schema::table('security_device_announcements', function (Blueprint $table) {
            $table->date('received_at')->nullable()->after('provider_id');
            $table->string('dispatch_number', 50)->nullable()->after('received_at');
            $table->foreignId('created_by')->nullable()->after('dispatch_number')->constrained('users');
        });

        foreach (DB::table('security_device_lots')->get() as $lot) {
            $announcement = DB::table('security_device_announcements')->where('id', $lot->security_device_announcement_id)->first();

            DB::table('security_device_lots')->where('id', $lot->id)->update([
                'security_device_type_id' => $announcement->security_device_type_id,
                'series_prefix' => $announcement->series_prefix,
                'min_digits' => $announcement->min_digits,
                'series_start' => $announcement->series_start,
                'series_end' => $announcement->series_end,
                'series_suffix' => $announcement->series_suffix,
            ]);

            DB::table('security_device_announcements')->where('id', $announcement->id)->update([
                'received_at' => $lot->received_at,
                'dispatch_number' => $lot->dispatch_number,
                'created_by' => $lot->created_by,
            ]);
        }

        Schema::table('security_device_announcements', function (Blueprint $table) {
            $table->dropForeign(['security_device_type_id']);
            $table->dropColumn(['security_device_type_id', 'series_prefix', 'min_digits', 'series_start', 'series_end', 'series_suffix']);
        });

        Schema::table('security_device_lots', function (Blueprint $table) {
            $table->dropForeign(['security_device_announcement_id']);
            $table->dropUnique(['security_device_announcement_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['received_at', 'dispatch_number', 'created_by']);
        });

        Schema::table('security_device_lots', function (Blueprint $table) {
            $table->foreign('security_device_announcement_id')->references('id')->on('security_device_announcements');
            $table->unsignedBigInteger('security_device_type_id')->nullable(false)->change();
            $table->string('series_prefix', 20)->nullable(false)->change();
            $table->unsignedTinyInteger('min_digits')->nullable(false)->change();
            $table->unsignedBigInteger('series_start')->nullable(false)->change();
            $table->unsignedBigInteger('series_end')->nullable(false)->change();
        });

        Schema::table('security_device_announcements', function (Blueprint $table) {
            $table->date('received_at')->nullable(false)->change();
            $table->string('dispatch_number', 50)->nullable(false)->change();
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations. Each announcement goes back to a single lot: its first one.
     */
    public function down(): void
    {
        Schema::table('security_device_announcements', function (Blueprint $table) {
            $table->foreignId('security_device_type_id')->nullable()->after('provider_id')->constrained();
            $table->string('series_prefix', 20)->nullable()->after('security_device_type_id');
            $table->unsignedTinyInteger('min_digits')->nullable()->after('series_prefix');
            $table->unsignedBigInteger('series_start')->nullable()->after('min_digits');
            $table->unsignedBigInteger('series_end')->nullable()->after('series_start');
            $table->string('series_suffix', 10)->nullable()->after('series_end');
        });

        Schema::table('security_device_lots', function (Blueprint $table) {
            $table->date('received_at')->nullable()->after('quantity');
            $table->string('dispatch_number', 50)->nullable()->after('received_at');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users');
        });

        foreach (DB::table('security_device_announcements')->get() as $announcement) {
            $lot = DB::table('security_device_lots')->where('security_device_announcement_id', $announcement->id)->orderBy('id')->first();

            if ($lot === null) {
                continue;
            }

            DB::table('security_device_announcements')->where('id', $announcement->id)->update([
                'security_device_type_id' => $lot->security_device_type_id,
                'series_prefix' => $lot->series_prefix,
                'min_digits' => $lot->min_digits,
                'series_start' => $lot->series_start,
                'series_end' => $lot->series_end,
                'series_suffix' => $lot->series_suffix,
            ]);

            DB::table('security_device_lots')->where('security_device_announcement_id', $announcement->id)->update([
                'received_at' => $announcement->received_at,
                'dispatch_number' => $announcement->dispatch_number,
                'created_by' => $announcement->created_by,
            ]);
        }

        Schema::table('security_device_lots', function (Blueprint $table) {
            $table->dropForeign(['security_device_type_id']);
            $table->dropColumn(['security_device_type_id', 'series_prefix', 'min_digits', 'series_start', 'series_end', 'series_suffix']);
            $table->unique('security_device_announcement_id');
        });

        Schema::table('security_device_announcements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['received_at', 'dispatch_number', 'created_by']);
        });
    }
};
