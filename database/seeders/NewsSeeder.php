<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Support\LocalizedSlug;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $tags = Tag::all();
        $writers = User::whereIn('role', ['writer', 'editor', 'admin'])->get();

        $newsItems = [
            [
                'title' => ['ar' => 'محافظ الضالع يفتتح مشروع إعادة تأهيل طريق قعطبة - الضالع', 'en' => 'Dhale Governor inaugurates Qataba-Dhale road rehabilitation project'],
                'content' => ['ar' => 'افتتح محافظ الضالع اليوم مشروع إعادة تأهيل طريق قعطبة - الضالع الرئيسي...', 'en' => 'The Governor inaugurated the road rehabilitation project today...'],
                'excerpt' => ['ar' => 'بتكلفة تتجاوز 2 مليار ريال يمني', 'en' => 'With a cost exceeding 2 billion Yemeni Riyals'],
                'status' => 'published', 'priority' => 'featured', 'location' => 'قعطبة',
                'source_name' => 'مكتب إعلام المحافظة',
            ],
            [
                'title' => ['ar' => 'أسعار الصرف تشهد انخفاضاً ملحوظاً في الضالع اليوم', 'en' => 'Exchange rates witness significant decline in Dhale today'],
                'content' => ['ar' => 'شهدت أسعار صرف العملات الأجنبية مقابل الريال اليمني انخفاضاً ملحوظاً اليوم في محافظة الضالع...', 'en' => 'Foreign currency exchange rates against the Yemeni Riyal witnessed a significant decline today...'],
                'excerpt' => ['ar' => 'الدولار يسجل 1,450 ريالاً والريال السعودي 385', 'en' => 'Dollar at 1,450 and Saudi Riyal at 385'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'إعلان نتائج الثانوية العامة في محافظة الضالع', 'en' => 'High school results announced in Dhale Governorate'],
                'content' => ['ar' => 'أعلنت إدارة التربية والتعليم بمحافظة الضالع اليوم نتائج الثانوية العامة للعام الدراسي...', 'en' => 'The Education Administration announced high school results today...'],
                'excerpt' => ['ar' => 'نسبة النجاح تتجاوز 78%', 'en' => 'Success rate exceeds 78%'],
                'status' => 'published', 'priority' => 'featured', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'مهرجان الضالع للتسوق ينطلق بمشاركة 150 تاجراً', 'en' => 'Dhale Shopping Festival kicks off with 150 merchants'],
                'content' => ['ar' => 'انطلقت اليوم فعاليات مهرجان الضالع للتسوق في نسخته الخامسة...', 'en' => 'The Dhale Shopping Festival launched today in its fifth edition...'],
                'excerpt' => ['ar' => 'خصومات تصل إلى 50%', 'en' => 'Discounts up to 50%'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'سيول الأمطار تجتاح مديرية جحاف وخسائر مادية كبيرة', 'en' => 'Floods hit Jahaf district causing major material losses'],
                'content' => ['ar' => 'اجتاحت سيول الأمطار الغزيرة مديرية جحاف...', 'en' => 'Heavy rain floods hit Jahaf district...'],
                'excerpt' => ['ar' => 'نداءات إنسانية عاجلة', 'en' => 'Urgent humanitarian appeals'],
                'status' => 'published', 'priority' => 'breaking', 'location' => 'جحاف',
            ],
            [
                'title' => ['ar' => 'افتتاح مركز صحي جديد في مديرية الأزارق', 'en' => 'New health center opens in Al-Azariq district'],
                'content' => ['ar' => 'افتتح اليوم مركز صحي جديد في مديرية الأزارق...', 'en' => 'A new health center opened today in Al-Azariq...'],
                'excerpt' => ['ar' => 'بتمويل من منظمة الصحة العالمية', 'en' => 'Funded by WHO'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الأزارق',
            ],
            [
                'title' => ['ar' => 'فريق نادي الضالع يتأهل لنهائي كأس المحافظة', 'en' => 'Dhale Club team qualifies for Governorate Cup final'],
                'content' => ['ar' => 'تأهل فريق نادي الضالع إلى نهائي كأس المحافظة...', 'en' => 'Dhale Club team qualified for the Governorate Cup final...'],
                'excerpt' => ['ar' => 'بفوز ساحق 4-0', 'en' => 'With a 4-0 victory'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'ورشة عمل حول الذكاء الاصطناعي في جامعة الضالع', 'en' => 'AI workshop at Dhale University'],
                'content' => ['ar' => 'نظمت جامعة الضالع اليوم ورشة عمل حول تطبيقات الذكاء الاصطناعي...', 'en' => 'Dhale University organized an AI workshop today...'],
                'excerpt' => ['ar' => 'بحضور 200 طالب وطالبة', 'en' => 'With 200 students attending'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'لقاء قبلي موسع في الشعيب يدعو للسلام', 'en' => 'Major tribal meeting in Al-Shuaib calls for peace'],
                'content' => ['ar' => 'عقد اليوم لقاء قبلي موسع في مديرية الشعيب...', 'en' => 'A major tribal meeting was held today in Al-Shuaib...'],
                'excerpt' => ['ar' => 'تأكيد على وحدة الصف', 'en' => 'Emphasis on unity'],
                'status' => 'published', 'priority' => 'featured', 'location' => 'الشعيب',
            ],
            [
                'title' => ['ar' => 'انقطاع التيار الكهربائي في دمت والمواطنون يحتجون', 'en' => 'Power outage in Damet and citizens protest'],
                'content' => ['ar' => 'شهدت مديرية دمت اليوم انقطاعاً للتيار الكهربائي...', 'en' => 'Damet district witnessed a power outage today...'],
                'excerpt' => ['ar' => 'لليوم الثالث على التوالي', 'en' => 'For the third consecutive day'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'دمت',
            ],
            [
                'title' => ['ar' => 'مبادرة شبابية لتشجير شوارع الضالع', 'en' => 'Youth initiative to plant trees on Dhale streets'],
                'content' => ['ar' => 'أطلقت مجموعة من الشباب المتطوعين مبادرة لتشجير شوارع مدينة الضالع...', 'en' => 'A group of youth volunteers launched an initiative...'],
                'excerpt' => ['ar' => '1000 شجرة في المرحلة الأولى', 'en' => '1000 trees in the first phase'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'ضبط عصابة تهريب آثار في الحصين', 'en' => 'Antiquities smuggling gang arrested in Al-Hussein'],
                'content' => ['ar' => 'تمكنت الأجهزة الأمنية من ضبط عصابة متخصصة في تهريب الآثار...', 'en' => 'Security forces arrested an antiquities smuggling gang...'],
                'excerpt' => ['ar' => 'ضبط 50 قطعة أثرية', 'en' => '50 artifacts seized'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الحصين',
            ],
            [
                'title' => ['ar' => 'بعد 3 أيام.. العثور على الطفل المفقود في جبال الضالع', 'en' => 'After 3 days.. missing child found in Dhale mountains'],
                'content' => ['ar' => 'عثرت فرق الإنقاذ اليوم على الطفل المفقود...', 'en' => 'Rescue teams found the missing child today...'],
                'excerpt' => ['ar' => 'بصحة جيدة', 'en' => 'In good health'],
                'status' => 'published', 'priority' => 'breaking', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'توقيع اتفاقية توأمة بين الضالع ومحافظة عربية', 'en' => 'Twinning agreement signed between Dhale and Arab governorate'],
                'content' => ['ar' => 'تم اليوم توقيع اتفاقية توأمة بين محافظة الضالع وإحدى المحافظات العربية...', 'en' => 'A twinning agreement was signed today...'],
                'excerpt' => ['ar' => 'في مجالات التنمية والاستثمار', 'en' => 'In development and investment'],
                'status' => 'published', 'priority' => 'normal', 'location' => 'الضالع',
            ],
            [
                'title' => ['ar' => 'الضالع أونلاين تحاور المحافظ حول خطة التنمية 2026', 'en' => 'Dhale Online interviews Governor on 2026 Development Plan'],
                'content' => ['ar' => 'أجرت الضالع أونلاين مقابلة حصرية مع محافظ الضالع...', 'en' => 'Dhale Online conducted an exclusive interview...'],
                'excerpt' => ['ar' => 'خطط طموحة للعام القادم', 'en' => 'Ambitious plans for next year'],
                'status' => 'published', 'priority' => 'editors_pick', 'location' => 'الضالع',
            ],
        ];

        foreach ($newsItems as $item) {
            $slug = LocalizedSlug::make($item['title']['ar']);
            $news = News::create(array_merge($item, [
                'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($item['title']['en'] ?? '', $item['title']['ar'])],
                'category_id' => $categories->random()->id,
                'user_id' => $writers->random()->id,
                'writer_id' => $writers->random()->id,
                'views_count' => rand(100, 5000),
                'shares_count' => rand(5, 200),
                'comments_count' => rand(0, 50),
                'reading_time' => rand(2, 10),
                'published_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]));

            // ربط وسوم عشوائية
            $news->tags()->sync($tags->random(rand(2, 5))->pluck('id'));
        }
    }
}
