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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();

            $table->text('content');
            $table->text('content_html')->nullable();

            $table->boolean('is_approved')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->integer('upvote_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('article_id');
            $table->index('parent_id');
        });

        Schema::create('comment_votes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->enum('vote_type', ['up', 'down']);
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['user_id', 'comment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_votes');
        Schema::dropIfExists('comments');
    }
};
