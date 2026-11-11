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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('identity_id', 50);
            $table->string('document_number', 15);
            $table->string('full_name', 255);
            $table->string('license', 15)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('identity_id')->references('id')->on('identities');
            $table->unique(['identity_id', 'document_number']);
            $table->index('full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
