<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('subtitle')->nullable();
            $table->json('content');
            $table->json('excerpt')->nullable();
            $table->string('main_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('featured_video')->nullable();
            $table->json('gallery')->nullable();

            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('writer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('draft');
            $table->string('priority')->default('normal');
            $table->string('format')->default('standard');

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('no_index')->default(false);

            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('source_name')->nullable();
            $table->string('source_url')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('unique_views')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('bookmarks_count')->default(0);
            $table->unsignedInteger('reading_time')->default(0);

            $table->boolean('allow_comments')->default(true);
            $table->boolean('is_sponsored')->default(false);
            $table->string('sponsored_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('breaking_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'published_at']);
            $table->index('priority');
            $table->index('format');
            $table->index('views_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
