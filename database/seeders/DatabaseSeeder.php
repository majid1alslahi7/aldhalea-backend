<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Interview;
use App\Models\Investigation;
use App\Models\News;
use App\Models\Poll;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [];

        if (!Setting::query()->exists()) $seeders[] = SettingSeeder::class;
        if (!User::query()->exists()) $seeders[] = UserSeeder::class;
        if (!Category::query()->exists()) $seeders[] = CategorySeeder::class;
        if (!Tag::query()->exists()) $seeders[] = TagSeeder::class;
        if (!News::query()->exists()) $seeders[] = NewsSeeder::class;
        if (!Article::query()->exists()) $seeders[] = ArticleSeeder::class;
        if (!Report::query()->exists()) $seeders[] = ReportSeeder::class;
        if (!Investigation::query()->exists()) $seeders[] = InvestigationSeeder::class;
        if (!Interview::query()->exists()) $seeders[] = InterviewSeeder::class;
        if (!Poll::query()->exists()) $seeders[] = PollSeeder::class;
        if (!Comment::query()->exists()) $seeders[] = CommentSeeder::class;
        if (!BreakingNews::query()->exists()) $seeders[] = BreakingNewsSeeder::class;

        $seeders[] = ExpandedRealContentSeeder::class;

        $this->call($seeders);
    }
}
