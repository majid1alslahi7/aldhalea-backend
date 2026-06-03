<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => ['ar' => 'الضالع', 'en' => 'Dhale'], 'slug' => ['ar' => 'الضالع', 'en' => 'dhale'], 'is_trending' => true],
            ['name' => ['ar' => 'اليمن', 'en' => 'Yemen'], 'slug' => ['ar' => 'اليمن', 'en' => 'yemen'], 'is_trending' => true],
            ['name' => ['ar' => 'قعطبة', 'en' => 'Qataba'], 'slug' => ['ar' => 'قعطبة', 'en' => 'qataba'], 'is_trending' => false],
            ['name' => ['ar' => 'دمت', 'en' => 'Damet'], 'slug' => ['ar' => 'دمت', 'en' => 'damet'], 'is_trending' => false],
            ['name' => ['ar' => 'جحاف', 'en' => 'Jahaf'], 'slug' => ['ar' => 'جحاف', 'en' => 'jahaf'], 'is_trending' => false],
            ['name' => ['ar' => 'الأزارق', 'en' => 'Al-Azariq'], 'slug' => ['ar' => 'الأزارق', 'en' => 'al-azariq'], 'is_trending' => false],
            ['name' => ['ar' => 'الشعيب', 'en' => 'Al-Shuaib'], 'slug' => ['ar' => 'الشعيب', 'en' => 'al-shuaib'], 'is_trending' => false],
            ['name' => ['ar' => 'الحصين', 'en' => 'Al-Hussein'], 'slug' => ['ar' => 'الحصين', 'en' => 'al-hussein'], 'is_trending' => false],
            ['name' => ['ar' => 'أسعار الصرف', 'en' => 'Exchange Rates'], 'slug' => ['ar' => 'اسعار-الصرف', 'en' => 'exchange-rates'], 'is_trending' => true],
            ['name' => ['ar' => 'تعليم', 'en' => 'Education'], 'slug' => ['ar' => 'تعليم', 'en' => 'education'], 'is_trending' => false],
            ['name' => ['ar' => 'صحة', 'en' => 'Health'], 'slug' => ['ar' => 'صحة', 'en' => 'health'], 'is_trending' => false],
            ['name' => ['ar' => 'كهرباء', 'en' => 'Electricity'], 'slug' => ['ar' => 'كهرباء', 'en' => 'electricity'], 'is_trending' => false],
            ['name' => ['ar' => 'مياه', 'en' => 'Water'], 'slug' => ['ar' => 'مياه', 'en' => 'water'], 'is_trending' => false],
            ['name' => ['ar' => 'طرقات', 'en' => 'Roads'], 'slug' => ['ar' => 'طرقات', 'en' => 'roads'], 'is_trending' => false],
            ['name' => ['ar' => 'زراعة', 'en' => 'Agriculture'], 'slug' => ['ar' => 'زراعة', 'en' => 'agriculture'], 'is_trending' => false],
            ['name' => ['ar' => 'رياضة', 'en' => 'Sports'], 'slug' => ['ar' => 'رياضة', 'en' => 'sports'], 'is_trending' => false],
            ['name' => ['ar' => 'سلام', 'en' => 'Peace'], 'slug' => ['ar' => 'سلام', 'en' => 'peace'], 'is_trending' => true],
            ['name' => ['ar' => 'حوار', 'en' => 'Dialogue'], 'slug' => ['ar' => 'حوار', 'en' => 'dialogue'], 'is_trending' => false],
            ['name' => ['ar' => 'ذكاء اصطناعي', 'en' => 'AI'], 'slug' => ['ar' => 'ذكاء-اصطناعي', 'en' => 'ai'], 'is_trending' => false],
            ['name' => ['ar' => 'موبايل', 'en' => 'Mobile'], 'slug' => ['ar' => 'موبايل', 'en' => 'mobile'], 'is_trending' => false],
        ];

        foreach ($tags as $tag) {
            Tag::create(array_merge($tag, ['color' => '#' . dechex(rand(0, 0xFFFFFF)), 'is_active' => true, 'news_count' => rand(3, 25)]));
        }
    }
}
