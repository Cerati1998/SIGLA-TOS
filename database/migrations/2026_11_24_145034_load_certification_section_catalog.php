<?php

use Database\Seeders\CertificationSectionCatalogSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations. Load the agreed catalog of sections (H33) with the deploy, which runs the migrations but not
     * the seeders (decision of the user of 2026-11-24). The automated tests skip it: they build their own sections.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        app(CertificationSectionCatalogSeeder::class)->run();
    }

    /**
     * Reverse the migrations. The catalog stays: by then the admin may have changed it or certifications may use it.
     */
    public function down(): void
    {
        //
    }
};
