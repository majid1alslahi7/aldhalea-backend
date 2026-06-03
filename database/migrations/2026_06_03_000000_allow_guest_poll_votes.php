<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->unique(['poll_id', 'user_id']);
            $table->index(['poll_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropIndex(['poll_id', 'ip_address']);
            $table->dropUnique(['poll_id', 'user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['poll_id', 'user_id']);
        });
    }
};
