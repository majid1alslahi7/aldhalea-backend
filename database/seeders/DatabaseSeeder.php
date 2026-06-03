<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            NewsSeeder::class,
            ArticleSeeder::class,
            ReportSeeder::class,
            InvestigationSeeder::class,
            InterviewSeeder::class,
            PollSeeder::class,
            CommentSeeder::class,
            BreakingNewsSeeder::class,
            ExpandedRealContentSeeder::class,
        ]);
    }
}
