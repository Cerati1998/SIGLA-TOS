<?php

use Database\Seeders\BranchSeeder;
use Database\Seeders\IdentitySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_id', 50)->nullable()->after('email');
            $table->string('document_number', 15)->nullable()->after('identity_id');
            $table->foreignId('branch_id')->nullable()->after('document_number');
        });

        $this->fillExistingUsers();

        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_id', 50)->nullable(false)->change();
            $table->string('document_number', 15)->nullable(false)->change();
            $table->foreignId('branch_id')->nullable(false)->change();

            $table->foreign('identity_id')->references('id')->on('identities');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unique(['identity_id', 'document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['identity_id']);
            $table->dropForeign(['branch_id']);
            $table->dropUnique(['identity_id', 'document_number']);
            $table->dropColumn(['identity_id', 'document_number', 'branch_id']);
        });
    }

    /**
     * Give the users that already exist a placeholder document and the main branch, to be corrected from the users screen.
     */
    private function fillExistingUsers(): void
    {
        if (! DB::table('users')->exists()) {
            return;
        }

        (new IdentitySeeder)->run();
        (new BranchSeeder)->run();

        DB::table('users')->update([
            'identity_id' => '-',
            'document_number' => DB::raw('id'),
            'branch_id' => DB::table('branches')->where('code', BranchSeeder::MAIN_BRANCH_CODE)->value('id'),
        ]);
    }
};
