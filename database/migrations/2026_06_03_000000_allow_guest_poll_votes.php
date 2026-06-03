<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropForeign(['poll_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unique(['poll_id', 'user_id']);
            $table->index(['poll_id', 'ip_address']);
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreign('poll_id')->references('id')->on('polls')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropIndex(['poll_id', 'ip_address']);
            $table->dropForeign(['poll_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique(['poll_id', 'user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unique(['poll_id', 'user_id']);
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreign('poll_id')->references('id')->on('polls')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
