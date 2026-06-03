<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => ['ar' => 'أخبار محلية', 'en' => 'Local News'],
                'slug' => ['ar' => 'اخبار-محلية', 'en' => 'local-news'],
                'description' => ['ar' => 'أخبار محافظة الضالع والمناطق المجاورة', 'en' => 'News from Dhale and surrounding areas'],
                'icon' => 'map-pin', 'color' => '#1E40AF', 'order' => 1,
            ],
            [
                'name' => ['ar' => 'سياسة', 'en' => 'Politics'],
                'slug' => ['ar' => 'سياسة', 'en' => 'politics'],
                'description' => ['ar' => 'تحليلات وأخبار سياسية محلية وإقليمية', 'en' => 'Political news and analysis'],
                'icon' => 'landmark', 'color' => '#DC2626', 'order' => 2,
            ],
            [
                'name' => ['ar' => 'اقتصاد', 'en' => 'Economy'],
                'slug' => ['ar' => 'اقتصاد', 'en' => 'economy'],
                'description' => ['ar' => 'أخبار اقتصادية، أسعار، استثمار', 'en' => 'Economic news, prices, investment'],
                'icon' => 'trending-up', 'color' => '#059669', 'order' => 3,
            ],
            [
                'name' => ['ar' => 'مجتمع', 'en' => 'Society'],
                'slug' => ['ar' => 'مجتمع', 'en' => 'society'],
                'description' => ['ar' => 'قضايا اجتماعية، تعليم، صحة', 'en' => 'Social issues, education, health'],
                'icon' => 'users', 'color' => '#7C3AED', 'order' => 4,
            ],
            [
                'name' => ['ar' => 'رياضة', 'en' => 'Sports'],
                'slug' => ['ar' => 'رياضة', 'en' => 'sports'],
                'description' => ['ar' => 'أخبار رياضية محلية وعالمية', 'en' => 'Local and international sports'],
                'icon' => 'trophy', 'color' => '#EA580C', 'order' => 5,
            ],
            [
                'name' => ['ar' => 'تكنولوجيا', 'en' => 'Technology'],
                'slug' => ['ar' => 'تكنولوجيا', 'en' => 'technology'],
                'description' => ['ar' => 'أخبار التقنية والابتكار', 'en' => 'Tech news and innovation'],
                'icon' => 'cpu', 'color' => '#2563EB', 'order' => 6,
            ],
            [
                'name' => ['ar' => 'ثقافة وفن', 'en' => 'Culture & Art'],
                'slug' => ['ar' => 'ثقافة-وفن', 'en' => 'culture-art'],
                'description' => ['ar' => 'فعاليات ثقافية، أدب، فنون', 'en' => 'Cultural events, literature, arts'],
                'icon' => 'palette', 'color' => '#DB2777', 'order' => 7,
            ],
            [
                'name' => ['ar' => 'تحقيقات', 'en' => 'Investigations'],
                'slug' => ['ar' => 'تحقيقات', 'en' => 'investigations'],
                'description' => ['ar' => 'تحقيقات استقصائية معمقة', 'en' => 'In-depth investigations'],
                'icon' => 'search', 'color' => '#4B5563', 'order' => 8,
            ],
            [
                'name' => ['ar' => 'تقارير مصورة', 'en' => 'Photo Reports'],
                'slug' => ['ar' => 'تقارير-مصورة', 'en' => 'photo-reports'],
                'description' => ['ar' => 'تقارير مصورة من قلب الحدث', 'en' => 'Photo reports from the field'],
                'icon' => 'camera', 'color' => '#0891B2', 'order' => 9,
            ],
            [
                'name' => ['ar' => 'منوعات', 'en' => 'Variety'],
                'slug' => ['ar' => 'منوعات', 'en' => 'variety'],
                'description' => ['ar' => 'منوعات وأخبار خفيفة', 'en' => 'Light news and variety'],
                'icon' => 'sparkles', 'color' => '#F59E0B', 'order' => 10,
            ],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['is_active' => true, 'show_in_menu' => true]));
        }
    }
}
