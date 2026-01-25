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
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('query', 500);
            $table->json('filters')->nullable();
            $table->integer('results_count')->default(0);
            $table->foreignId('clicked_article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('query');
            $table->index('created_at');
        });

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('page_url', 500);
            $table->string('referrer', 500)->nullable();

            $table->string('session_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country_code', 2)->nullable();

            $table->integer('time_on_page')->nullable()->comment('Seconds');
            $table->integer('scroll_depth')->nullable()->comment('Percentage');

            $table->timestamp('created_at')->useCurrent();

            $table->index('article_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('search_logs');
    }
};
