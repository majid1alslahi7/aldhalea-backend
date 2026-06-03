<?php

namespace Database\Seeders;

use App\Models\Investigation;
use App\Models\Category;
use App\Models\User;
use App\Support\LocalizedSlug;
use Illuminate\Database\Seeder;

class InvestigationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['editor', 'admin'])->get();
        $categories = Category::all();

        $investigations = [
            ['title' => ['ar' => 'تحقيق استقصائي: تهريب الآثار اليمنية عبر الحدود', 'en' => 'Investigative Report: Smuggling of Yemeni Antiquities'], 'location' => 'الحصين', 'priority' => 'urgent'],
            ['title' => ['ar' => 'تحقيق: الفساد في توزيع المساعدات الإنسانية', 'en' => 'Investigation: Corruption in Humanitarian Aid Distribution'], 'location' => 'الضالع', 'priority' => 'featured'],
            ['title' => ['ar' => 'تحقيق: تجنيد الأطفال في النزاعات المسلحة', 'en' => 'Investigation: Child Recruitment in Armed Conflicts'], 'location' => 'الضالع', 'priority' => 'urgent'],
            ['title' => ['ar' => 'تحقيق: الأراضي المنهوبة في مديريات الضالع', 'en' => 'Investigation: Looted Lands in Dhale Districts'], 'location' => 'قعطبة', 'priority' => 'featured'],
            ['title' => ['ar' => 'تحقيق: تجارة المخدرات في المحافظات الجنوبية', 'en' => 'Investigation: Drug Trade in Southern Governorates'], 'location' => 'الضالع', 'priority' => 'urgent'],
        ];

        foreach ($investigations as $item) {
            $slug = LocalizedSlug::make($item['title']['ar']);
            Investigation::create(array_merge($item, [
                'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($item['title']['en'] ?? '', $item['title']['ar'])],
                'category_id' => $categories->random()->id,
                'user_id' => $users->random()->id,
                'status' => 'published',
                'content' => ['ar' => 'محتوى التحقيق الاستقصائي الكامل...', 'en' => 'Full investigation content...'],
                'excerpt' => ['ar' => 'ملخص التحقيق', 'en' => 'Investigation summary'],
                'views_count' => rand(200, 4000),
                'published_at' => now()->subDays(rand(0, 60)),
            ]));
        }
    }
}
