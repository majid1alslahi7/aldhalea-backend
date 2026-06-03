<?php

use Database\Seeders\ExpandedRealContentSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ExpandedRealContentSeeder::class)->run();
    }

    public function down(): void
    {
        // Keep editorial content in place if the migration is rolled back.
    }
};
