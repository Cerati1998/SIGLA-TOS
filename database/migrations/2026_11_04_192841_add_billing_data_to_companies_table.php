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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('ubigeo');
            $table->string('detraction_account')->nullable()->after('logo_path');
            $table->text('invoice_header')->nullable()->after('detraction_account');
            $table->text('invoice_footer')->nullable()->after('invoice_header');
            $table->string('sol_user')->nullable()->after('invoice_footer');
            $table->text('sol_pass')->nullable()->after('sol_user');
            $table->string('client_id')->nullable()->after('sol_pass');
            $table->text('client_secret')->nullable()->after('client_id');
            $table->text('certificate')->nullable()->after('client_secret');
            $table->boolean('production')->default(false)->after('certificate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'detraction_account',
                'invoice_header',
                'invoice_footer',
                'sol_user',
                'sol_pass',
                'client_id',
                'client_secret',
                'certificate',
                'production',
            ]);
        });
    }
};
