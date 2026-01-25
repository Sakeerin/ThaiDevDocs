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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            // Basic Info
            $table->string('title', 500);
            $table->string('slug', 500);
            $table->text('summary')->nullable();

            // Content
            $table->longText('content')->comment('Markdown content');
            $table->longText('content_html')->nullable()->comment('Rendered HTML');
            $table->json('table_of_contents')->nullable()->comment('Auto-generated TOC');

            // Classification
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('article_type', ['guide', 'reference', 'tutorial', 'example', 'glossary'])->default('guide');

            // Statistics
            $table->integer('reading_time')->default(0)->comment('Minutes');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->integer('bookmark_count')->default(0);

            // Status
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('allow_comments')->default(true);

            // Dates
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 500)->nullable();

            // Version Control
            $table->integer('current_version')->default(1);

            $table->unique(['topic_id', 'slug']);
            $table->index('status');
            $table->index('published_at');
            $table->index('is_featured');
            $table->index('difficulty');
            $table->fullText(['title', 'summary', 'content'], 'articles_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
