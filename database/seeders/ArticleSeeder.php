<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $writers = User::whereIn('role', ['writer', 'editor'])->get();
        $tags = Tag::all();

        $articles = [
            [
                'title' => ['ar' => 'مستقبل التنمية في الضالع: رؤية استراتيجية', 'en' => 'Future of Development in Dhale: A Strategic Vision'],
                'content' => ['ar' => 'في ظل التحديات الراهنة التي تواجه محافظة الضالع، تبرز الحاجة الملحة لوضع رؤية استراتيجية شاملة للتنمية...', 'en' => 'Amid current challenges facing Dhale, there is an urgent need for a comprehensive strategic vision...'],
                'excerpt' => ['ar' => 'قراءة تحليلية في آفاق التنمية بالمحافظة', 'en' => 'Analytical reading of development prospects'],
                'type' => 'analysis', 'priority' => 'featured',
            ],
            [
                'title' => ['ar' => 'الشباب اليمني بين الواقع والطموح', 'en' => 'Yemeni Youth Between Reality and Ambition'],
                'content' => ['ar' => 'يمثل الشباب اليمني النسبة الأكبر من المجتمع، ويواجه تحديات جسيمة...', 'en' => 'Yemeni youth represent the largest segment of society...'],
                'excerpt' => ['ar' => 'تحديات وحلول', 'en' => 'Challenges and solutions'],
                'type' => 'opinion', 'priority' => 'normal',
            ],
            [
                'title' => ['ar' => 'أثر التكنولوجيا على التعليم في المناطق الريفية', 'en' => 'Impact of Technology on Rural Education'],
                'content' => ['ar' => 'تشهد المناطق الريفية في محافظة الضالع تحولاً تدريجياً نحو التعليم الرقمي...', 'en' => 'Rural areas in Dhale are witnessing a gradual shift towards digital education...'],
                'excerpt' => ['ar' => 'تجارب من مديريات الضالع', 'en' => 'Experiences from Dhale districts'],
                'type' => 'blog', 'priority' => 'normal',
            ],
            [
                'title' => ['ar' => 'المرأة اليمنية شريك أساسي في بناء السلام', 'en' => 'Yemeni Women: Essential Partners in Peacebuilding'],
                'content' => ['ar' => 'أثبتت المرأة اليمنية عبر التاريخ أنها عنصر فاعل وأساسي في بناء المجتمع...', 'en' => 'Yemeni women have proven throughout history to be an active element...'],
                'excerpt' => ['ar' => 'دور المرأة في المجتمع', 'en' => 'Role of women in society'],
                'type' => 'column', 'priority' => 'editors_pick',
            ],
            [
                'title' => ['ar' => 'الاستثمار في القطاع الزراعي بالضالع: فرص وتحديات', 'en' => 'Investment in Dhale Agricultural Sector: Opportunities and Challenges'],
                'content' => ['ar' => 'تتمتع محافظة الضالع بمقومات زراعية هائلة تجعلها من أهم المناطق الزراعية...', 'en' => 'Dhale has enormous agricultural potential...'],
                'excerpt' => ['ar' => 'الضالع سلة الغذاء', 'en' => 'Dhale the food basket'],
                'type' => 'feature', 'priority' => 'featured',
            ],
            [
                'title' => ['ar' => 'قراءة في المشهد السياسي اليمني المعاصر', 'en' => 'Reading the Contemporary Yemeni Political Scene'],
                'content' => ['ar' => 'يمر المشهد السياسي اليمني بمرحلة مفصلية تستدعي قراءة متأنية...', 'en' => 'The Yemeni political scene is going through a critical phase...'],
                'excerpt' => ['ar' => 'تحليل سياسي', 'en' => 'Political analysis'],
                'type' => 'analysis', 'priority' => 'normal',
            ],
            [
                'title' => ['ar' => 'الموروث الثقافي في جبال الضالع', 'en' => 'Cultural Heritage in Dhale Mountains'],
                'content' => ['ar' => 'تزخر جبال الضالع بموروث ثقافي وحضاري يعود لآلاف السنين...', 'en' => 'Dhale mountains are rich in cultural heritage dating back thousands of years...'],
                'excerpt' => ['ar' => 'كنوز ثقافية', 'en' => 'Cultural treasures'],
                'type' => 'blog', 'priority' => 'normal',
            ],
            [
                'title' => ['ar' => 'اقتصاد المحافظات اليمنية: حالة الضالع نموذجاً', 'en' => 'Economy of Yemeni Governorates: Dhale as a Case Study'],
                'content' => ['ar' => 'يمكن اعتبار اقتصاد محافظة الضالع نموذجاً مصغراً للاقتصاد اليمني...', 'en' => 'Dhale economy can be considered a microcosm of the Yemeni economy...'],
                'excerpt' => ['ar' => 'تحليل اقتصادي', 'en' => 'Economic analysis'],
                'type' => 'analysis', 'priority' => 'normal',
            ],
        ];

        foreach ($articles as $item) {
            $slug = Str::slug($item['title']['ar']);
            $article = Article::create(array_merge($item, [
                'slug' => ['ar' => $slug, 'en' => Str::slug($item['title']['en'] ?? '')],
                'writer_id' => $writers->random()->id,
                'status' => 'published',
                'views_count' => rand(50, 3000),
                'shares_count' => rand(3, 150),
                'comments_count' => rand(0, 30),
                'reading_time' => rand(3, 12),
                'published_at' => now()->subDays(rand(0, 60))->subHours(rand(0, 23)),
            ]));

            $article->tags()->sync($tags->random(rand(2, 4))->pluck('id'));
        }
    }
}
