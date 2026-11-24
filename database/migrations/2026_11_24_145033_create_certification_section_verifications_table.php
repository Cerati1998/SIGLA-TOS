<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Each row links a checkpoint section of verification with an internal or external section,
     * whose serial numbers it shows to the checkpoint inspector (H33).
     */
    public function up(): void
    {
        Schema::create('certification_section_verifications', function (Blueprint $table) {
            $table->foreignId('checkpoint_section_id')->constrained('certification_sections', indexName: 'section_verifications_checkpoint_foreign');
            $table->foreignId('verified_section_id')->constrained('certification_sections', indexName: 'section_verifications_verified_foreign');

            $table->primary(['checkpoint_section_id', 'verified_section_id'], 'section_verifications_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_section_verifications');
    }
};
