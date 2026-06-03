<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['editor', 'writer'])->get();
        $categories = Category::all();

        $reports = [
            ['title' => ['ar' => 'تقرير مصور: أسواق الضالع في رمضان', 'en' => 'Photo Report: Dhale Markets in Ramadan'], 'type' => 'photo', 'location' => 'الضالع'],
            ['title' => ['ar' => 'تقرير: واقع الخدمات الصحية في مديريات الضالع', 'en' => 'Report: Health Services Reality in Dhale Districts'], 'type' => 'written', 'location' => 'الضالع'],
            ['title' => ['ar' => 'فيديو: جولة في وادي بناء السياحي', 'en' => 'Video: Tour of Wadi Bana Tourist Area'], 'type' => 'video', 'location' => 'الضالع'],
            ['title' => ['ar' => 'تقرير مصور: معاناة النازحين في الضالع', 'en' => 'Photo Report: Plight of Displaced in Dhale'], 'type' => 'photo', 'location' => 'الضالع'],
            ['title' => ['ar' => 'تقرير: واقع التعليم في الريف', 'en' => 'Report: Education Reality in Rural Areas'], 'type' => 'written', 'location' => 'قعطبة'],
            ['title' => ['ar' => 'فيديو: حصاد البن اليمني في جبال الضالع', 'en' => 'Video: Yemeni Coffee Harvest in Dhale Mountains'], 'type' => 'video', 'location' => 'الضالع'],
            ['title' => ['ar' => 'تقرير: أزمة المياه في دمت', 'en' => 'Report: Water Crisis in Damet'], 'type' => 'written', 'location' => 'دمت'],
            ['title' => ['ar' => 'تقرير مصور: مهرجان الضالع للتراث', 'en' => 'Photo Report: Dhale Heritage Festival'], 'type' => 'photo', 'location' => 'الضالع'],
        ];

        foreach ($reports as $item) {
            $slug = Str::slug($item['title']['ar']);
            Report::create(array_merge($item, [
                'slug' => ['ar' => $slug, 'en' => Str::slug($item['title']['en'] ?? '')],
                'category_id' => $categories->random()->id,
                'user_id' => $users->random()->id,
                'status' => 'published',
                'content' => ['ar' => 'محتوى التقرير الكامل...', 'en' => 'Full report content...'],
                'excerpt' => ['ar' => 'ملخص التقرير', 'en' => 'Report summary'],
                'views_count' => rand(100, 3000),
                'published_at' => now()->subDays(rand(0, 45)),
            ]));
        }
    }
}
