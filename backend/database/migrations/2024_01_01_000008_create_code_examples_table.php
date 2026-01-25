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
        Schema::create('code_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('language', 50)->comment('html, css, javascript, php, etc.');

            $table->longText('code');
            $table->text('output')->nullable();
            $table->enum('output_type', ['text', 'html', 'console', 'image'])->default('text');

            $table->boolean('is_runnable')->default(false);
            $table->boolean('is_editable')->default(false);
            $table->json('sandbox_config')->nullable()->comment('Config for code sandbox');

            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('article_id');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_examples');
    }
};
