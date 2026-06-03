<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('subtitle')->nullable();
            $table->json('content');
            $table->json('excerpt')->nullable();
            $table->string('main_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('gallery')->nullable();
            $table->json('documents')->nullable();
            $table->json('evidence')->nullable();

            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('draft');
            $table->string('priority')->default('normal');

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->string('location')->nullable();
            $table->json('people_involved')->nullable();
            $table->date('investigation_date')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->unsignedInteger('reading_time')->default(0);

            $table->boolean('allow_comments')->default(true);
            $table->boolean('is_confidential')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigations');
    }
};
