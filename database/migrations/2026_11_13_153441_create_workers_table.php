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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('identity_id', 50);
            $table->string('document_number', 15);
            $table->string('full_name', 255);
            $table->boolean('is_own');
            $table->foreignId('provider_id')->nullable()->constrained('providers');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('identity_id')->references('id')->on('identities');
            $table->unique(['identity_id', 'document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
