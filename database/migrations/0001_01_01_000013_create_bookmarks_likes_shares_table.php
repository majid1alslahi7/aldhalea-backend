<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bookmarkable_type');
            $table->unsignedBigInteger('bookmarkable_id');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id']);
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('likeable_type');
            $table->unsignedBigInteger('likeable_id');
            $table->string('type')->default('like');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'likeable_type', 'likeable_id']);
        });

        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');
            $table->string('platform')->default('other');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['shareable_type', 'shareable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shares');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('bookmarks');
    }
};
