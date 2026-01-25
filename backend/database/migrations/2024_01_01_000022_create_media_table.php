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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('filename');
            $table->string('original_filename');
            $table->string('path', 500);
            $table->string('disk', 50)->default('public');

            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size')->comment('Bytes');

            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
