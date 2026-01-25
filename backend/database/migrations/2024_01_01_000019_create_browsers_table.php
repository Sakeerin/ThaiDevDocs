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
        Schema::create('browsers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('browser_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('browser_id')->constrained()->cascadeOnDelete();

            $table->enum('support_status', ['yes', 'no', 'partial', 'unknown'])->default('unknown');
            $table->string('version_added', 50)->nullable();
            $table->string('version_removed', 50)->nullable();
            $table->text('notes')->nullable();
            $table->json('flags')->nullable();

            $table->timestamps();

            $table->unique(['article_id', 'browser_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('browser_compatibility');
        Schema::dropIfExists('browsers');
    }
};
