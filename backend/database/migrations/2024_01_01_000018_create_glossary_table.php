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
        Schema::create('glossary', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->string('term_en')->nullable();
            $table->string('slug')->unique();

            $table->text('definition');
            $table->string('definition_short', 500)->nullable();

            $table->string('pronunciation')->nullable();
            $table->text('etymology')->nullable();

            $table->json('related_terms')->nullable();
            $table->json('external_links')->nullable();

            $table->boolean('is_approved')->default(true);
            $table->integer('view_count')->default(0);

            $table->timestamps();

            $table->fullText(['term', 'term_en', 'definition'], 'glossary_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glossary');
    }
};
