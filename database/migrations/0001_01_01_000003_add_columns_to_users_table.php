<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('role')->default('reader')->after('avatar');
            $table->text('bio')->nullable()->after('role');
            $table->string('location')->nullable()->after('bio');
            $table->string('website')->nullable()->after('location');
            $table->json('social_links')->nullable()->after('website');
            $table->boolean('is_active')->default(true)->after('social_links');
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('is_verified');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'phone', 'avatar', 'role', 'bio',
                'location', 'website', 'social_links', 'is_active',
                'is_verified', 'last_login_at', 'last_login_ip'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
