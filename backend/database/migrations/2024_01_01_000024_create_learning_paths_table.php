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
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('estimated_hours')->nullable();

            $table->string('thumbnail', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);

            $table->integer('enrollment_count')->default(0);
            $table->integer('completion_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();

            $table->timestamps();
        });

        Schema::create('learning_path_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();

            $table->integer('sort_order');
            $table->boolean('is_required')->default(true);
            $table->text('notes')->nullable();

            $table->unique(['learning_path_id', 'article_id']);
        });

        Schema::create('user_learning_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();

            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('current_item_id')->nullable()->constrained('learning_path_items')->nullOnDelete();
            $table->integer('progress_percentage')->default(0);

            $table->unique(['user_id', 'learning_path_id']);
        });

        Schema::create('user_completed_items', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_path_item_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->useCurrent();

            $table->primary(['user_id', 'learning_path_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_completed_items');
        Schema::dropIfExists('user_learning_progress');
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
    }
};
