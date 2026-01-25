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
        Schema::create('article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->longText('content');
            $table->longText('content_html')->nullable();
            $table->string('change_summary', 500)->nullable();

            $table->integer('version');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_major')->default(false);

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['article_id', 'version']);
            $table->index(['article_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_revisions');
    }
};
