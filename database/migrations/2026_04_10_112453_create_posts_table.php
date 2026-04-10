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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('html')->nullable();
            $table->longText('plaintext')->nullable();
            $table->longText('markdown')->nullable();
            $table->text('custom_excerpt')->nullable();
            $table->string('feature_image')->nullable();
            $table->string('feature_image_alt')->nullable();
            $table->text('feature_image_caption')->nullable();
            $table->string('type')->default('post')->index();
            $table->string('status')->default('draft')->index();
            $table->unsignedSmallInteger('reading_time')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('ghost_id')->nullable()->index();
            $table->timestamps();

            $table->index(['type', 'status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
