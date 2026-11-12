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
        Schema::create('certification_section_security_device_type', function (Blueprint $table) {
            $table->foreignId('certification_section_id')->constrained(indexName: 'certification_section_device_type_section_foreign');
            $table->foreignId('security_device_type_id')->constrained(indexName: 'certification_section_device_type_type_foreign');
            $table->unsignedSmallInteger('quantity');

            $table->primary(['certification_section_id', 'security_device_type_id'], 'certification_section_device_type_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_section_security_device_type');
    }
};
