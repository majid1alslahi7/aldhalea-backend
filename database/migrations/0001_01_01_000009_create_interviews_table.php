<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('content');
            $table->json('excerpt')->nullable();
            $table->string('main_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('featured_video')->nullable();

            $table->string('interviewee_name');
            $table->string('interviewee_title')->nullable();
            $table->string('interviewee_photo')->nullable();
            $table->text('interviewee_bio')->nullable();
            $table->json('interviewee_social')->nullable();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('draft');
            $table->string('type')->default('text');
            $table->string('priority')->default('normal');

            $table->string('location')->nullable();
            $table->date('interview_date')->nullable();
            $table->integer('duration')->nullable();

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('shares_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);

            $table->boolean('allow_comments')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
