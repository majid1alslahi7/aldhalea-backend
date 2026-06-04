<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->string('url', 2048)->nullable()->after('platform');
            $table->string('referer', 2048)->nullable()->after('ip_address');
            $table->string('user_agent', 500)->nullable()->after('referer');
        });

        Schema::create('content_correction_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reportable_type')->nullable();
            $table->unsignedBigInteger('reportable_id')->nullable();
            $table->string('content_type')->nullable();
            $table->string('content_title')->nullable();
            $table->string('url', 2048);
            $table->string('reason')->default('correction');
            $table->text('details');
            $table->string('evidence_url', 2048)->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('status')->default('pending');
            $table->text('editor_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_correction_reports');

        Schema::table('shares', function (Blueprint $table) {
            $table->dropColumn(['url', 'referer', 'user_agent']);
        });
    }
};
