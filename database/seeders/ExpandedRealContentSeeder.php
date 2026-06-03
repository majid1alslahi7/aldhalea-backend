<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\News;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use App\Support\LocalizedSlug;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ExpandedRealContentSeeder extends Seeder
{
    private const MARKER = 'aldhalea-real-content-2026';

    private const IMAGES = [
        'water' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
        'solar' => 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?auto=format&fit=crop&w=1200&q=80',
        'roads' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80',
        'humanitarian' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1200&q=80',
        'education' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80',
        'health' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1200&q=80',
        'economy' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=1200&q=80',
        'sports' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=1200&q=80',
        'tech' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
        'culture' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&w=1200&q=80',
        'default' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80',
    ];

    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();

        if (!$admin) {
            return;
        }

        $writers = User::whereIn('role', ['writer', 'editor', 'admin'])->get();
        $writer = $writers->first() ?? $admin;

        foreach ($this->newsItems() as $index => $item) {
            $this->seedNewsItem($item, $admin, $writer, $index);
        }

        foreach ($this->articleItems() as $index => $item) {
            $this->seedArticleItem($item, $writer, $index);
        }

        foreach ($this->reportItems() as $index => $item) {
            $this->seedReportItem($item, $admin, $index);
        }

        Category::all()->each(fn (Category $category) => $category->updateNewsCount());
        Tag::all()->each(fn (Tag $tag) => $tag->updateNewsCount());
        Cache::flush();
    }

    private function seedNewsItem(array $item, User $admin, User $writer, int $index): void
    {
        $category = $this->category($item['category']);
        if (!$category) {
            return;
        }

        $slug = LocalizedSlug::make($item['title']);
        $news = News::withTrashed()->where('slug->ar', $slug)->first();
        $publishedAt = Carbon::parse($item['published_at']);
        $tags = $item['tags'] ?? [];

        $payload = [
            'title' => ['ar' => $item['title'], 'en' => $item['title_en'] ?? $item['title']],
            'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($item['title_en'] ?? $item['title'], $item['title'])],
            'subtitle' => ['ar' => $item['subtitle'] ?? '', 'en' => $item['subtitle_en'] ?? ($item['subtitle'] ?? '')],
            'content' => $this->content($item['lead'], $item['points'], $item['closing'] ?? null, $item['source_name'], $item['source_url']),
            'excerpt' => ['ar' => $item['excerpt'], 'en' => $item['excerpt_en'] ?? $item['excerpt']],
            'main_image' => self::IMAGES[$item['image'] ?? 'default'] ?? self::IMAGES['default'],
            'thumbnail' => self::IMAGES[$item['image'] ?? 'default'] ?? self::IMAGES['default'],
            'category_id' => $category->id,
            'user_id' => $admin->id,
            'writer_id' => $writer->id,
            'status' => 'published',
            'priority' => $item['priority'] ?? 'normal',
            'format' => $item['format'] ?? 'standard',
            'meta_title' => ['ar' => $item['title'], 'en' => $item['title_en'] ?? $item['title']],
            'meta_description' => ['ar' => $item['excerpt'], 'en' => $item['excerpt_en'] ?? $item['excerpt']],
            'meta_keywords' => self::MARKER . ',' . implode(',', $tags),
            'location' => $item['location'] ?? 'اليمن',
            'city' => $item['city'] ?? null,
            'district' => $item['district'] ?? null,
            'source_name' => $item['source_name'],
            'source_url' => $item['source_url'],
            'views_count' => $item['views'] ?? (900 + (($index + 3) * 173)),
            'unique_views' => $item['unique_views'] ?? (600 + (($index + 1) * 91)),
            'shares_count' => $item['shares'] ?? (25 + (($index + 2) * 7)),
            'comments_count' => $item['comments'] ?? (($index * 3) % 48),
            'likes_count' => $item['likes'] ?? (40 + (($index + 1) * 11)),
            'reading_time' => $item['reading_time'] ?? 5,
            'published_at' => $publishedAt,
            'breaking_until' => ($item['priority'] ?? null) === 'breaking' ? now()->addDays(10) : null,
        ];

        if ($news) {
            $news->restore();
            $news->update($payload);
        } else {
            $news = News::create($payload);
        }

        $news->tags()->sync($this->tagIds($tags));
    }

    private function seedArticleItem(array $item, User $writer, int $index): void
    {
        $category = $this->category($item['category']);
        $slug = LocalizedSlug::make($item['title']);
        $article = Article::withTrashed()->where('slug->ar', $slug)->first();
        $tags = $item['tags'] ?? [];

        $payload = [
            'title' => ['ar' => $item['title'], 'en' => $item['title_en'] ?? $item['title']],
            'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($item['title_en'] ?? $item['title'], $item['title'])],
            'subtitle' => ['ar' => $item['subtitle'] ?? '', 'en' => $item['subtitle_en'] ?? ($item['subtitle'] ?? '')],
            'content' => $this->content($item['lead'], $item['points'], $item['closing'] ?? null, $item['source_name'], $item['source_url']),
            'excerpt' => ['ar' => $item['excerpt'], 'en' => $item['excerpt_en'] ?? $item['excerpt']],
            'featured_image' => self::IMAGES[$item['image'] ?? 'default'] ?? self::IMAGES['default'],
            'thumbnail' => self::IMAGES[$item['image'] ?? 'default'] ?? self::IMAGES['default'],
            'category_id' => $category?->id,
            'writer_id' => $writer->id,
            'status' => 'published',
            'type' => $item['type'] ?? 'analysis',
            'priority' => $item['priority'] ?? 'normal',
            'meta_title' => ['ar' => $item['title'], 'en' => $item['title_en'] ?? $item['title']],
            'meta_description' => ['ar' => $item['excerpt'], 'en' => $item['excerpt_en'] ?? $item['excerpt']],
            'meta_keywords' => self::MARKER . ',' . implode(',', $tags),
            'views_count' => 700 + (($index + 1) * 151),
            'shares_count' => 19 + (($index + 1) * 8),
            'comments_count' => ($index * 5) % 34,
            'likes_count' => 33 + (($index + 2) * 9),
            'reading_time' => $item['reading_time'] ?? 7,
            'published_at' => Carbon::parse($item['published_at']),
        ];

        if ($article) {
            $article->restore();
            $article->update($payload);
        } else {
            $article = Article::create($payload);
        }

        $article->tags()->sync($this->tagIds($tags));
    }

    private function seedReportItem(array $item, User $admin, int $index): void
    {
        $category = $this->category($item['category']);
        if (!$category) {
            return;
        }

        $slug = LocalizedSlug::make($item['title']);
        $report = Report::withTrashed()->where('slug->ar', $slug)->first();

        $payload = [
            'title' => ['ar' => $item['title'], 'en' => $item['title_en'] ?? $item['title']],
            'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($item['title_en'] ?? $item['title'], $item['title'])],
            'content' => $this->content($item['lead'], $item['points'], $item['closing'] ?? null, $item['source_name'], $item['source_url']),
            'excerpt' => ['ar' => $item['excerpt'], 'en' => $item['excerpt_en'] ?? $item['excerpt']],
            'main_image' => self::IMAGES[$item['image'] ?? 'default'] ?? self::IMAGES['default'],
            'thumbnail' => self::IMAGES[$item['image'] ?? 'default'] ?? self::IMAGES['default'],
            'category_id' => $category->id,
            'user_id' => $admin->id,
            'status' => 'published',
            'type' => $item['type'] ?? 'written',
            'priority' => $item['priority'] ?? 'normal',
            'meta_keywords' => self::MARKER,
            'location' => $item['location'] ?? 'اليمن',
            'source_name' => $item['source_name'],
            'source_url' => $item['source_url'],
            'views_count' => 500 + (($index + 1) * 189),
            'shares_count' => 15 + (($index + 1) * 6),
            'comments_count' => ($index * 4) % 28,
            'likes_count' => 21 + (($index + 1) * 7),
            'reading_time' => $item['reading_time'] ?? 6,
            'published_at' => Carbon::parse($item['published_at']),
        ];

        if ($report) {
            $report->restore();
            $report->update($payload);
        } else {
            Report::create($payload);
        }
    }

    private function content(string $lead, array $points, ?string $closing, string $sourceName, string $sourceUrl): array
    {
        $html = '<p>' . $this->escape($lead) . '</p>';
        $html .= '<ul>';

        foreach ($points as $point) {
            $html .= '<li>' . $this->escape($point) . '</li>';
        }

        $html .= '</ul>';

        if ($closing) {
            $html .= '<p>' . $this->escape($closing) . '</p>';
        }

        $html .= '<p><strong>مصدر المعلومات:</strong> ' . $this->escape($sourceName) . ' - ' . $this->escape($sourceUrl) . '</p>';

        return ['ar' => $html, 'en' => strip_tags($html)];
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function category(string $slug): ?Category
    {
        return Category::where('slug->ar', $slug)->orWhere('slug->en', $slug)->first();
    }

    private function tagIds(array $names): array
    {
        return collect($names)->map(fn (string $name) => $this->tag($name)->id)->all();
    }

    private function tag(string $name): Tag
    {
        $slug = LocalizedSlug::make($name);
        $tag = Tag::where('slug->ar', $slug)->first();

        if ($tag) {
            $tag->update([
                'name' => ['ar' => $name, 'en' => $name],
                'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($name)],
                'is_active' => true,
            ]);

            return $tag;
        }

        return Tag::create([
            'name' => ['ar' => $name, 'en' => $name],
            'slug' => ['ar' => $slug, 'en' => LocalizedSlug::make($name)],
            'description' => ['ar' => 'وسم تحريري مرتبط بتغطيات الضالع أونلاين', 'en' => 'Editorial tag'],
            'color' => '#2563EB',
            'is_active' => true,
            'is_trending' => in_array($name, ['اليمن', 'الأمن الغذائي', 'المياه', 'الضالع'], true),
        ]);
    }

    private function newsItems(): array
    {
        return [
            [
                'category' => 'اخبار-محلية',
                'title' => 'مشروع طاقة شمسية يعزز ضخ المياه في قرى حجر وسناح بالضالع',
                'title_en' => 'Solar power project supports water pumping in Hajar and Sanah',
                'subtitle' => 'حل محلي لأزمة المياه والكهرباء عبر منظومة شمسية بقدرة 280 كيلوواط',
                'excerpt' => 'تقرير محلي يوثق تشغيل منظومة شمسية لضخ المياه في مناطق ريفية من الضالع، ضمن توجه لتخفيف كلفة الوقود والانقطاعات.',
                'lead' => 'أعادت مشاريع الطاقة الشمسية الصغيرة طرح سؤال الخدمات الأساسية في الضالع من زاوية عملية: كيف يمكن لمضخات المياه أن تستمر عندما تتراجع الكهرباء والوقود؟ تشير تغطيات محلية إلى تشغيل منظومة شمسية بقدرة تقارب 280 كيلوواط لخدمة آبار ومناطق في حجر وسناح.',
                'points' => [
                    'المشروع مهم لأنه لا يكتفي بتوفير طاقة بديلة، بل يقلل زمن توقف الضخ ويخفف تكلفة الديزل على لجان المياه والأهالي.',
                    'الاستفادة الأكبر تظهر في القرى التي تعتمد على آبار عميقة أو شبكات نقل طويلة، حيث تؤدي ساعات التشغيل الإضافية إلى انتظام التوزيع.',
                    'يبقى التحدي في الصيانة، وحماية الألواح، وتوفير بطاريات أو حلول تشغيل تضمن استمرار الخدمة في أيام الغبار والغيوم.',
                    'الخبر يضع ملف المياه والطاقة في مسار واحد: لا يمكن معالجة العطش في المناطق الجبلية من دون طاقة مستقرة ورخيصة.',
                ],
                'closing' => 'توصي الضالع أونلاين بتوسيع نشر بيانات مشاريع المياه: عدد المستفيدين، ساعات الضخ، تكلفة الصيانة، ومصادر التمويل، حتى يتمكن المواطن من تقييم الأثر الحقيقي.',
                'source_name' => 'عدن تايم',
                'source_url' => 'https://www.aden-tm.net/news/310068',
                'published_at' => '2026-04-07 09:00:00',
                'location' => 'حجر وسناح - الضالع',
                'image' => 'solar',
                'priority' => 'featured',
                'tags' => ['الضالع', 'المياه', 'طاقة شمسية', 'خدمات'],
                'reading_time' => 6,
            ],
            [
                'category' => 'اخبار-محلية',
                'title' => 'خزان مياه جديد في لكمة الدوكي يفتح نافذة أمل للأهالي',
                'title_en' => 'New water tank in Lakmat Al Doki supports local access',
                'subtitle' => 'سعة 125 مترا مكعبا ضمن تدخلات خدمية للحد من شح المياه',
                'excerpt' => 'افتتاح خزان مياه في منطقة لكمة الدوكي يعكس حاجة المديريات الريفية إلى حلول تخزين وتوزيع أكثر استدامة.',
                'lead' => 'في المناطق الجبلية لا تعني المياه وجود بئر فقط، بل وجود تخزين آمن وشبكة توزيع قادرة على إيصال المياه للأسر. نشرت مصادر محلية خبرا عن افتتاح خزان مياه في لكمة الدوكي بسعة 125 مترا مكعبا، وهو تدخل صغير في حجمه الإنشائي لكنه كبير في أثره اليومي.',
                'points' => [
                    'الخزان يساعد على تنظيم التوزيع بين الحارات وتقليل الاعتماد على النقل العشوائي بالصهاريج.',
                    'السعة التخزينية تمنح المجتمع المحلي هامشا للتعامل مع الأعطال أو تأخر الضخ.',
                    'الاحتياج القادم هو ربط الخزان بجدول تشغيل معلن وآلية رقابة مجتمعية تمنع سوء الاستخدام.',
                    'هذا النوع من المشاريع يصلح كنموذج للتكرار في قرى أخرى بشرط وجود صيانة وتمويل تشغيل واضح.',
                ],
                'closing' => 'تغطية المياه في الضالع يجب أن تنتقل من خبر افتتاح المشروع إلى متابعة ما بعد التشغيل: هل وصلت المياه؟ كم مرة في الأسبوع؟ وما حجم الأعطال؟',
                'source_name' => 'صوت الضالع',
                'source_url' => 'https://sautaldalea.com/archives/94557',
                'published_at' => '2025-05-26 11:30:00',
                'location' => 'لكمة الدوكي - الضالع',
                'image' => 'water',
                'priority' => 'normal',
                'tags' => ['الضالع', 'المياه', 'ريف', 'خدمات'],
                'reading_time' => 5,
            ],
            [
                'category' => 'اخبار-محلية',
                'title' => 'أضرار الأمطار في طريق الحقل وبن عواس تعيد ملف طرق الأزارق إلى الواجهة',
                'title_en' => 'Rain damage on Al Haql and Bin Awas road revives Al Azariq roads file',
                'subtitle' => 'انزلاقات صخرية وطرق ريفية تحتاج صيانة وقائية قبل موسم الأمطار',
                'excerpt' => 'تضرر طريق ريفي في الأزارق بفعل الأمطار يسلط الضوء على حاجة الطرق الجبلية إلى صيانة ومصارف مياه.',
                'lead' => 'تفقد مسؤول محلي في الضالع أضرارا لحقت بالطريق الرابط بين منطقة الحقل وجبل بن عواس وعدد من القرى الريفية في مديرية الأزارق بعد انزلاقات صخرية ناجمة عن أمطار غزيرة. يعيد الخبر ملف الطرق الريفية إلى صدارة احتياجات الخدمات في المناطق الجبلية.',
                'points' => [
                    'الطرق الريفية في الأزارق ليست ممرات نقل فقط، بل طريق المريض إلى المركز الصحي والطالب إلى المدرسة والسلعة إلى السوق.',
                    'الأمطار تكشف غالبا نقاط الضعف: انعدام مصارف المياه، تراكم الصخور، وضيق المنعطفات في الجبال.',
                    'الصيانة الوقائية قبل الموسم أقل كلفة من انتظار الانهيار ثم إعلان الطوارئ.',
                    'توثيق النقاط المتضررة بالصور والإحداثيات يمكن أن يساعد السلطات والمنظمات على ترتيب الأولويات.',
                ],
                'closing' => 'الطريق الآمن خدمة أساسية؛ فهو يخفض كلفة الغذاء، ويقرب العلاج، ويحمي حياة الناس عند السيول.',
                'source_name' => 'وكالة سبأ عبر نبض',
                'source_url' => 'https://nabd.com/s/123302285-9b7919/',
                'published_at' => '2023-08-06 10:20:00',
                'location' => 'الأزارق - الضالع',
                'image' => 'roads',
                'priority' => 'normal',
                'tags' => ['الأزارق', 'طرقات', 'الضالع', 'سيول'],
                'reading_time' => 5,
            ],
            [
                'category' => 'سياسة',
                'title' => 'اتفاق أممي جديد لتبادل 1600 محتجز ينعش ملف الثقة في اليمن',
                'title_en' => 'UN backed agreement to release 1600 detainees revives confidence file in Yemen',
                'subtitle' => 'اللجنة الإشرافية أعلنت التوصل لاتفاق إفراج واسع في مايو 2026',
                'excerpt' => 'ملف المحتجزين يعود كبوابة إنسانية وسياسية يمكن أن تفتح الطريق أمام خطوات أوسع في مسار السلام.',
                'lead' => 'أعلنت الأمم المتحدة في جنيف في 14 مايو 2026 أن الأطراف اليمنية توصلت إلى اتفاق لإطلاق سراح 1600 محتجز على خلفية النزاع. وعلى الرغم من أن الاتفاق إنساني في جوهره، فإن أثره السياسي يتجاوز أبواب السجون لأنه يعيد اختبار قدرة الأطراف على تنفيذ التزامات ملموسة.',
                'points' => [
                    'نجاح عملية الإفراج سيعطي العائلات أملا مباشرا، وسيخلق مؤشرا على قابلية الأطراف للالتزام بإجراءات بناء الثقة.',
                    'الضالع، بحكم موقعها وحساسيتها الأمنية، تتابع مثل هذه الملفات لأنها تؤثر على المزاج العام ومسارات التهدئة المحلية.',
                    'التحدي لا ينتهي عند إعلان الاتفاق؛ التنفيذ يحتاج قوائم دقيقة، آليات تحقق، وضمانات تمنع الانتقائية.',
                    'الملف يذكر بأن السياسة في اليمن لا تنفصل عن احتياجات الناس اليومية: أسرة تنتظر مفقودا، وقرية تنتظر خبرا مطمئنا.',
                ],
                'closing' => 'تتعامل الضالع أونلاين مع ملف الأسرى والمحتجزين كقضية إنسانية أولا، وكاختبار سياسي لمسار بناء الثقة ثانيا.',
                'source_name' => 'الأمم المتحدة - جنيف',
                'source_url' => 'https://www.ungeneva.org/en/news-media/news/2026/05/118661/yemen-parties-agree-under-un-mediation-release-1600-detainees',
                'published_at' => '2026-05-14 15:00:00',
                'location' => 'اليمن',
                'image' => 'humanitarian',
                'priority' => 'breaking',
                'tags' => ['اليمن', 'سياسة', 'السلام', 'محتجزون'],
                'reading_time' => 6,
            ],
            [
                'category' => 'سياسة',
                'title' => 'المبعوث الأممي يحذر من هشاشة الهدوء ويدعو لمسار يمني شامل',
                'title_en' => 'UN envoy warns fragile calm needs inclusive Yemeni process',
                'subtitle' => 'إحاطة سياسية تؤكد أن التهدئة لا تكفي من دون عملية جامعة',
                'excerpt' => 'إحاطات الأمم المتحدة تضع الهدوء العسكري في ميزان السياسة: الفرصة قائمة، لكنها هشة وتحتاج مسارا يمنيا شاملا.',
                'lead' => 'تؤكد إحاطات مكتب المبعوث الأممي إلى اليمن أن أي هدوء ميداني يظل هشا ما لم يتحول إلى عملية سياسية جامعة تراعي ملفات الاقتصاد والخدمات والأمن المحلي. هذه القراءة مهمة للضالع لأن المحافظة تقع عند تقاطع سياسي وأمني شديد الحساسية.',
                'points' => [
                    'المسار السياسي لا يقاس بعدد اللقاءات فقط، بل بقدرته على خفض التوتر في المحافظات وفتح قنوات محلية للحوار.',
                    'غياب الخدمات يضغط على السياسة؛ فالكهرباء والمياه والرواتب وأسعار الصرف تتحول بسرعة إلى مطالب احتجاجية.',
                    'تحتاج المحافظات الجنوبية إلى تمثيل واضح في أي نقاشات مستقبلية، لأن واقع الأرض لا يسمح بحلول عامة من دون تفاصيل محلية.',
                    'الضالع نموذج لمنطقة تحتاج تهدئة أمنية وتنمية خدمية في الوقت نفسه.',
                ],
                'closing' => 'الهدوء فرصة، لكنه يصبح قابلا للاستمرار عندما يلمس المواطن أثرا في السوق والمدرسة والمستشفى والطريق.',
                'source_name' => 'مكتب المبعوث الخاص للأمين العام إلى اليمن',
                'source_url' => 'https://osesgy.unmissions.org/',
                'published_at' => '2026-01-15 12:00:00',
                'location' => 'اليمن',
                'image' => 'default',
                'priority' => 'featured',
                'tags' => ['اليمن', 'سياسة', 'الأمم المتحدة', 'السلام'],
                'reading_time' => 6,
            ],
            [
                'category' => 'سياسة',
                'title' => 'التوتر في الجنوب يعيد سؤال الإدارة المحلية والخدمات إلى المقدمة',
                'title_en' => 'Southern Yemen tensions renew local governance and services debate',
                'subtitle' => 'السياسة المحلية تظهر في تفاصيل الخدمات اليومية قبل البيانات الرسمية',
                'excerpt' => 'في الجنوب اليمني يتداخل السياسي بالخدمي، وتصبح الكهرباء والمياه والرواتب جزءا من النقاش حول الإدارة والتمثيل.',
                'lead' => 'لا تظهر السياسة في المحافظات الجنوبية عبر الخطابات فقط؛ تظهر كذلك في طوابير الوقود، وفواتير النقل، وشكاوى المستشفيات، وأزمات الكهرباء. ولهذا فإن أي قراءة للمشهد في الضالع يجب أن تربط بين الأمن والخدمة والاقتصاد.',
                'points' => [
                    'التنافس السياسي يزداد حدة عندما تتراجع الخدمات، لأن المواطن يبحث عمن يتحمل مسؤولية الانقطاع والغلاء.',
                    'الإدارة المحلية تحتاج بيانات مفتوحة عن الإيرادات والإنفاق والمشاريع حتى لا تتحول الشائعات إلى مصدر المعلومة الوحيد.',
                    'الاستقرار الأمني في الضالع يرتبط بقدرة السلطات والمجتمع على حل النزاعات الصغيرة قبل تراكمها.',
                    'كل نقاش سياسي بلا خطة خدمات قابلة للقياس يظل بعيدا عن احتياج الناس.',
                ],
                'closing' => 'الخبر السياسي الحقيقي في حياة الناس يبدأ أحيانا من مضخة مياه تعمل، أو مدرسة تفتح أبوابها، أو طريق يصبح آمنا.',
                'source_name' => 'تحليل تحريري يستند إلى إحاطات أممية وتقارير إنسانية',
                'source_url' => 'https://press.un.org/en/yemen',
                'published_at' => '2026-02-20 09:40:00',
                'location' => 'جنوب اليمن',
                'image' => 'roads',
                'priority' => 'normal',
                'tags' => ['سياسة', 'جنوب اليمن', 'خدمات', 'الضالع'],
                'reading_time' => 6,
            ],
            [
                'category' => 'اقتصاد',
                'title' => 'البنك الدولي: الاقتصاد اليمني ما زال تحت ضغط الحرب وانقسام المؤسسات',
                'title_en' => 'World Bank says Yemen economy remains under war and institutional pressure',
                'subtitle' => 'قراءة في مؤشرات الانكماش والعملة والقدرة الشرائية',
                'excerpt' => 'تحديثات البنك الدولي عن اليمن تشرح لماذا يشعر المواطن في الضالع بضغط الأسعار حتى عند استقرار بعض السلع مؤقتا.',
                'lead' => 'يربط البنك الدولي تدهور الاقتصاد اليمني باستمرار الصراع، وانقسام المؤسسات، وتراجع الصادرات والإيرادات العامة. بالنسبة للأسر في الضالع، تظهر هذه الصورة الكبيرة في تفاصيل صغيرة: سعر السلة الغذائية، أجرة المواصلات، وقدرة الراتب على تغطية الشهر.',
                'points' => [
                    'تراجع النشاط الاقتصادي يعني فرص عمل أقل، وخصوصا للشباب في المدن الثانوية والريف.',
                    'تذبذب العملة ينعكس مباشرة على أسعار الغذاء والدواء والوقود، وهي سلع مستوردة أو مرتبطة بالدولار.',
                    'انقسام المؤسسات المالية يضعف قدرة السياسات العامة على ضبط السوق وتوحيد الإجراءات.',
                    'تحتاج المحافظة إلى نشر أسعار يومية موثقة للسلع والعملات حتى يحصل المواطن والتاجر على مرجعية شفافة.',
                ],
                'closing' => 'اقتصاد الضالع ليس معزولا عن اقتصاد اليمن؛ لكنه يحتاج أدوات محلية للرصد: سوق، عملة، نقل، دخل، وفرص عمل.',
                'source_name' => 'البنك الدولي',
                'source_url' => 'https://www.worldbank.org/en/country/yemen/publication/yemen-economic-monitor',
                'published_at' => '2026-05-21 08:30:00',
                'location' => 'اليمن',
                'image' => 'economy',
                'priority' => 'featured',
                'tags' => ['اقتصاد', 'اليمن', 'أسعار الصرف', 'دخل'],
                'reading_time' => 7,
            ],
            [
                'category' => 'اقتصاد',
                'title' => 'الأمن الغذائي في اليمن يضغط على موائد الأسر في الضالع',
                'title_en' => 'Food insecurity in Yemen pressures household tables in Dhale',
                'subtitle' => 'تحذيرات دولية من اتساع فجوة الغذاء في مناطق الحكومة المعترف بها',
                'excerpt' => 'تقارير الأمن الغذائي تكشف أن أزمة الطعام لم تعد رقما وطنيا بعيدا، بل معادلة يومية داخل كل بيت.',
                'lead' => 'توضح تحديثات التصنيف المرحلي المتكامل للأمن الغذائي وشركائه أن ملايين اليمنيين يواجهون مستويات مرتفعة من انعدام الأمن الغذائي، خاصة مع تراجع الدخل وارتفاع الأسعار. في الضالع، يصبح هذا المؤشر سؤالا مباشرا: كم وجبة تستطيع الأسرة تأمينها؟',
                'points' => [
                    'الأسر الفقيرة تتأثر أولا بسعر الدقيق والزيت والغاز، ثم تنتقل الأزمة إلى الصحة والتعليم عندما تقلل الأسرة نفقاتها الأخرى.',
                    'تراجع المساعدات أو تأخرها يضاعف الضغط على الأسواق المحلية لأن الطلب يبقى موجودا والدخل يتآكل.',
                    'التعامل مع الأزمة يحتاج معلومات محلية عن أسعار السلة الغذائية لا مجرد أرقام وطنية عامة.',
                    'الزراعة المنزلية، دعم صغار المنتجين، وتنظيم الأسواق الشعبية قد تخفف بعض الضغط إذا ارتبطت بخطط جدية.',
                ],
                'closing' => 'كل رقم عن الأمن الغذائي يجب أن يتحول إلى سؤال ميداني: أين ترتفع الأسعار أكثر؟ ومن هي الأسر التي خرجت من شبكة الحماية؟',
                'source_name' => 'IPC وشركاء الأمن الغذائي في اليمن',
                'source_url' => 'https://www.ipcinfo.org/ipc-country-analysis/details-map/en/c/1163308/?iso3=YEM',
                'published_at' => '2026-02-18 10:00:00',
                'location' => 'اليمن',
                'image' => 'humanitarian',
                'priority' => 'trending',
                'tags' => ['الأمن الغذائي', 'اقتصاد', 'اليمن', 'الضالع'],
                'reading_time' => 7,
            ],
            [
                'category' => 'اقتصاد',
                'title' => 'أسعار النقل والوقود تضاعف كلفة الوصول إلى الخدمات في مديريات الضالع',
                'title_en' => 'Transport and fuel costs raise access burden in Dhale districts',
                'subtitle' => 'قراءة اقتصادية في المسافة بين القرية والمستشفى والسوق',
                'excerpt' => 'ارتفاع النقل ليس رقما في السوق فقط؛ إنه حاجز بين المواطن والمدرسة والمشفى وفرصة العمل.',
                'lead' => 'تظهر كلفة الوقود والنقل في الضالع كعامل اقتصادي واجتماعي في الوقت نفسه. فالأسرة التي تعيش في قرية بعيدة لا تدفع فقط ثمن السلعة، بل تدفع ثمن وصولها إلى السوق، وثمن انتقال المريض إلى المستشفى، وثمن وصول الطالب إلى المدرسة.',
                'points' => [
                    'الطرق الجبلية تزيد استهلاك الوقود وتسرع أعطال المركبات، وهو ما ينعكس على أجرة النقل.',
                    'ارتفاع كلفة النقل يرفع أسعار السلع الغذائية لأن التاجر ينقل الكلفة إلى المستهلك.',
                    'تحسين الطرق الريفية يمكن أن يكون سياسة اقتصادية لا مجرد مشروع بنية تحتية.',
                    'أي تدخل في الأسواق يجب أن يقيس أثر النقل بين المديريات لا سعر السلعة في مركز المدينة فقط.',
                ],
                'closing' => 'في الضالع، الطريق الجيد يمكن أن يخفض الفقر بطريقة غير مباشرة: يقلل كلفة الغذاء، ويفتح السوق، ويقرب الخدمة.',
                'source_name' => 'البنك الدولي وبرنامج الأغذية العالمي',
                'source_url' => 'https://www.wfp.org/countries/yemen',
                'published_at' => '2026-03-03 12:20:00',
                'location' => 'الضالع',
                'image' => 'roads',
                'priority' => 'normal',
                'tags' => ['اقتصاد', 'نقل', 'طرقات', 'الضالع'],
                'reading_time' => 6,
            ],
            [
                'category' => 'مجتمع',
                'title' => 'اليونيسف: ملايين الأطفال في اليمن يحتاجون دعما تعليميا عاجلا',
                'title_en' => 'UNICEF says millions of Yemeni children need urgent education support',
                'subtitle' => 'أزمة التعليم تنعكس على مدارس الضالع والريف اليمني',
                'excerpt' => 'أرقام التعليم في اليمن تكشف أن المدرسة أصبحت خط دفاع اجتماعي أمام الفقر والنزوح والعمل المبكر.',
                'lead' => 'تؤكد اليونيسف أن ملايين الأطفال في سن الدراسة في اليمن يحتاجون إلى دعم تعليمي، وأن آلاف المدارس تضررت أو توقفت جزئيا بسبب سنوات النزاع. في الضالع، لا تبدو الأزمة بعيدة: المدارس الريفية تواجه نقص الكادر والكتاب والمقاعد والقدرة على استمرار الطلاب.',
                'points' => [
                    'تراجع دخل الأسر يدفع بعض الطلاب إلى العمل أو الانقطاع، خصوصا في المراحل المتوسطة والثانوية.',
                    'غياب البيئة المدرسية الآمنة يضعف انتظام الفتيات أكثر في المناطق البعيدة.',
                    'المدرسة تحتاج إلى ماء وصرف صحي وكهرباء ومقاعد، لا إلى منهج فقط.',
                    'المعالجة الجادة تبدأ من خريطة مدرسية تكشف المدارس الأكثر احتياجا في كل مديرية.',
                ],
                'closing' => 'التعليم في الضالع ليس ملفا موسميا مع بداية العام الدراسي، بل قضية مستقبل المحافظة كله.',
                'source_name' => 'اليونيسف - اليمن',
                'source_url' => 'https://www.unicef.org/yemen/education',
                'published_at' => '2026-01-24 09:10:00',
                'location' => 'اليمن',
                'image' => 'education',
                'priority' => 'featured',
                'tags' => ['تعليم', 'مجتمع', 'اليمن', 'الأطفال'],
                'reading_time' => 7,
            ],
            [
                'category' => 'مجتمع',
                'title' => 'احتياجات المياه والصرف الصحي تجعل الوقاية الصحية أولوية في الضالع',
                'title_en' => 'Water and sanitation needs make prevention a priority in Dhale',
                'subtitle' => 'أزمات المياه تزيد مخاطر الأمراض المنقولة بالماء',
                'excerpt' => 'الوقاية من الأوبئة تبدأ من نقطة ماء آمنة، وصرف صحي، ورسائل توعية تصل إلى القرى.',
                'lead' => 'تربط اليونيسف ومنظمات إنسانية بين ضعف خدمات المياه والصرف الصحي وزيادة مخاطر الأمراض المنقولة بالماء في اليمن. وفي الضالع، حيث تتباعد القرى وتتعثر مشاريع المياه، تصبح الوقاية الصحية مسؤولية مشتركة بين الأسرة والمدرسة والمركز الصحي.',
                'points' => [
                    'المياه غير الآمنة قد تحول أزمة الخدمة إلى أزمة صحية، خصوصا بين الأطفال وكبار السن.',
                    'نقاط الكلور والتوعية بالنظافة وغسل اليدين ليست تفاصيل ثانوية، بل أدوات وقاية منخفضة الكلفة.',
                    'المدارس والمخيمات والأسواق تحتاج مرافق صحية تعمل باستمرار لا أثناء الحملات فقط.',
                    'متابعة جودة المياه يجب أن ترافق مشاريع الآبار والخزانات حتى لا تتحول البنية إلى مصدر خطر.',
                ],
                'closing' => 'التغطية المجتمعية الناجحة لا تكتفي بسؤال: هل وصل الماء؟ بل تسأل أيضا: هل هو آمن؟',
                'source_name' => 'اليونيسف - المياه والإصحاح في اليمن',
                'source_url' => 'https://www.unicef.org/yemen/water-sanitation-and-hygiene',
                'published_at' => '2026-02-04 08:45:00',
                'location' => 'اليمن',
                'image' => 'water',
                'priority' => 'normal',
                'tags' => ['المياه', 'صحة', 'مجتمع', 'وقاية'],
                'reading_time' => 6,
            ],
            [
                'category' => 'مجتمع',
                'title' => 'منظمة الصحة العالمية تطلب تمويلا عاجلا لدعم الخدمات الصحية في اليمن',
                'title_en' => 'WHO seeks urgent funding to support health services in Yemen',
                'subtitle' => 'المرافق الصحية المحلية تواجه ضغط المرض والفقر ونقص التمويل',
                'excerpt' => 'تراجع التمويل الصحي في اليمن ينعكس على توفر الدواء واللقاحات وخدمات الطوارئ في المحافظات.',
                'lead' => 'أعلنت منظمة الصحة العالمية احتياجها إلى تمويل عاجل لدعم تدخلاتها الصحية في اليمن خلال 2026. وتبرز أهمية هذا النداء في محافظات مثل الضالع حيث يعتمد كثير من السكان على مرافق صحية محدودة الإمكانات ومثقلة بالأمراض المزمنة والطوارئ.',
                'points' => [
                    'نقص التمويل يعني غالبا نقص الأدوية الأساسية، وتراجع قدرة المرافق على استقبال الحالات الحرجة.',
                    'برامج التحصين ومراقبة الأوبئة تحتاج استمرارا؛ أي انقطاع قد يظهر لاحقا على شكل تفشيات مكلفة.',
                    'المناطق البعيدة تحتاج عيادات متنقلة وإحالة طبية منظمة لا تعتمد على قدرة الأسرة على دفع النقل.',
                    'الصحة ملف اجتماعي واقتصادي لأن المرض يدفع الأسر إلى بيع أصولها أو إيقاف تعليم أبنائها.',
                ],
                'closing' => 'الصحة في الضالع تحتاج رصدا شهريا: الدواء، الكادر، الكهرباء، المياه، والإحالة إلى المستشفيات الأكبر.',
                'source_name' => 'منظمة الصحة العالمية',
                'source_url' => 'https://www.who.int/publications/m/item/yemen--who-health-emergency-appeal-2026',
                'published_at' => '2026-01-30 13:00:00',
                'location' => 'اليمن',
                'image' => 'health',
                'priority' => 'normal',
                'tags' => ['صحة', 'اليمن', 'مجتمع', 'تمويل'],
                'reading_time' => 6,
            ],
            [
                'category' => 'رياضة',
                'title' => 'المنتخب اليمني يواصل مشوار تصفيات كأس آسيا وسط آمال جماهيرية واسعة',
                'title_en' => 'Yemen continues Asian Cup qualifying campaign amid public hopes',
                'subtitle' => 'كرة القدم تظل مساحة فرح نادرة في يوميات اليمنيين',
                'excerpt' => 'رغم الظروف الصعبة، تحافظ مباريات المنتخب على حضور جماهيري ورمزي كبير لدى اليمنيين في الداخل والخارج.',
                'lead' => 'تمنح مباريات المنتخب اليمني في التصفيات الآسيوية مساحة متابعة تتجاوز الرياضة. فكل مباراة تتحول إلى حديث في المقاهي والبيوت ومجموعات الهاتف، لأنها تقدم لحظة وحدة وفرح وسط أخبار اقتصادية وإنسانية ثقيلة.',
                'points' => [
                    'الاستحقاقات الآسيوية تمنح اللاعبين الشباب فرصة احتكاك دولي مهمة رغم محدودية البنية الرياضية المحلية.',
                    'الجمهور في المحافظات، ومنها الضالع، يتابع المنتخب كرمز وطني يتجاوز الانقسام السياسي.',
                    'الكرة اليمنية تحتاج دوريات مستقرة، ملاعب آمنة، ورعاية للفئات السنية حتى لا تبقى المواهب رهينة الصدفة.',
                    'تغطية الرياضة محليا ينبغي أن تربط المنتخب بالأندية والمدارس والمبادرات الشبابية في المديريات.',
                ],
                'closing' => 'الرياضة في اليمن ليست ترفا؛ إنها واحدة من قليل المساحات التي تجمع الناس حول أمل مشترك.',
                'source_name' => 'الاتحاد الآسيوي لكرة القدم',
                'source_url' => 'https://www.the-afc.com/en/national/afc_asian_cup.html',
                'published_at' => '2026-03-25 18:30:00',
                'location' => 'اليمن',
                'image' => 'sports',
                'priority' => 'featured',
                'tags' => ['رياضة', 'المنتخب اليمني', 'كأس آسيا', 'شباب'],
                'reading_time' => 5,
            ],
            [
                'category' => 'رياضة',
                'title' => 'دوريات المدارس في الضالع يمكن أن تكون بوابة لاكتشاف المواهب',
                'title_en' => 'School leagues in Dhale can become a talent gateway',
                'subtitle' => 'الرياضة المدرسية تربط التعليم بالصحة والانضباط',
                'excerpt' => 'إحياء المنافسات المدرسية المنظمة قد يمنح الأطفال والشباب مساحة آمنة للتنافس والتعلم.',
                'lead' => 'تحتاج الرياضة في الضالع إلى نقطة بداية قريبة من المجتمع، والمدرسة هي المكان الأنسب. فالدوريات المدرسية لا تصنع لاعبين فقط، بل تساعد الطلاب على الانضباط، وبناء الثقة، وتخفيف ضغوط الحياة اليومية.',
                'points' => [
                    'المنافسات المدرسية قليلة التكلفة مقارنة بإنشاء أندية كبيرة، لكنها تحتاج تنظيما وحكاما وملاعب آمنة.',
                    'إشراك الفتيات في أنشطة مناسبة وآمنة يوسع معنى الرياضة ويعزز الصحة العامة.',
                    'تعاون المدارس مع الأندية المحلية يمكن أن يحول البطولة من حدث عابر إلى مسار اكتشاف.',
                    'الدعم المجتمعي البسيط، مثل توفير الكرات والقمصان والمواصلات، يصنع أثرا واضحا.',
                ],
                'closing' => 'الاستثمار في الرياضة المدرسية استثمار في الصحة والسلوك والانتماء، وليس في النتائج فقط.',
                'source_name' => 'تحليل تحريري يستند إلى برامج الرياضة المدرسية الدولية',
                'source_url' => 'https://www.unicef.org/sports-for-development',
                'published_at' => '2026-02-12 16:00:00',
                'location' => 'الضالع',
                'image' => 'sports',
                'priority' => 'normal',
                'tags' => ['رياضة', 'تعليم', 'شباب', 'الضالع'],
                'reading_time' => 5,
            ],
            [
                'category' => 'رياضة',
                'title' => 'ملاعب الأحياء في الضالع بين شغف الشباب وحاجة التنظيم',
                'title_en' => 'Neighborhood pitches in Dhale between youth passion and organization needs',
                'subtitle' => 'مساحات اللعب الشعبية تحتاج سلامة وجدولة ورعاية محلية',
                'excerpt' => 'ملاعب الأحياء ليست مجرد فراغات ترابية؛ إنها مركز اجتماعي يومي للشباب وتستحق إدارة أفضل.',
                'lead' => 'في كثير من أحياء الضالع تتحول الساحات الصغيرة إلى ملاعب مساء كل يوم. هذه المساحات تكشف شغف الشباب بالرياضة، لكنها تكشف أيضا الحاجة إلى تنظيم يحمي اللاعبين ويمنع النزاعات ويجعل اللعب فرصة لا مشكلة.',
                'points' => [
                    'الملاعب الشعبية تحتاج إنارة آمنة وتسوية أرضية وإبعاد المخاطر القريبة من خطوط السير.',
                    'جدولة الفرق وتحديد مسؤول مجتمعي لكل ملعب يمكن أن يقلل الاحتكاكات.',
                    'رعاية تجارية صغيرة من محلات محلية قد تمول بطولات قصيرة وتخلق علاقة إيجابية بين السوق والشباب.',
                    'تغطية هذه الأنشطة إعلاميا تشجع المواهب وتمنح الشباب شعورا بأن جهدهم مرئي.',
                ],
                'closing' => 'كل ملعب حي منظم هو مساحة أمان اجتماعي قبل أن يكون مساحة رياضية.',
                'source_name' => 'ملف تحريري محلي',
                'source_url' => 'https://www.fifa.com/social-impact',
                'published_at' => '2026-02-03 17:15:00',
                'location' => 'الضالع',
                'image' => 'sports',
                'priority' => 'normal',
                'tags' => ['رياضة', 'شباب', 'ملاعب', 'مجتمع'],
                'reading_time' => 5,
            ],
            [
                'category' => 'تكنولوجيا',
                'title' => 'الفجوة الرقمية في اليمن تجعل الإنترنت خدمة تنموية لا ترفا',
                'title_en' => 'Yemen digital divide makes internet a development service',
                'subtitle' => 'الاتصال الضعيف يحد من التعليم والعمل والخدمات المالية',
                'excerpt' => 'ضعف الاتصال في اليمن لا يعني بطء التصفح فقط، بل يعني فرصا أقل في التعليم والعمل والوصول إلى المعلومة.',
                'lead' => 'تؤكد تقارير التنمية الرقمية أن الاتصال بالإنترنت في البيئات الهشة يصبح خدمة أساسية: الطالب يحتاجه للتعلم، والتاجر للتسويق، والمغترب للتواصل، والمواطن للوصول إلى الخدمات. في الضالع، تظهر الفجوة الرقمية بوضوح بين المدينة والقرية.',
                'points' => [
                    'ضعف الشبكة يحد من قدرة الطلاب على متابعة الدروس والمواد المفتوحة.',
                    'ارتفاع تكلفة البيانات يجعل الإنترنت عبئا على الأسر ذات الدخل المحدود.',
                    'المشاريع الرقمية الحكومية أو الإنسانية تفشل إذا افترضت اتصالا مستقرا لدى الجميع.',
                    'المطلوب خرائط تغطية شفافة ومبادرات واي فاي مجتمعية في المدارس والمكتبات والمراكز الشبابية.',
                ],
                'closing' => 'التكنولوجيا في الضالع تبدأ من سؤال بسيط: هل يستطيع المواطن الاتصال أصلا؟',
                'source_name' => 'GSMA Mobile Connectivity Index',
                'source_url' => 'https://www.mobileconnectivityindex.com/',
                'published_at' => '2026-03-18 10:00:00',
                'location' => 'اليمن',
                'image' => 'tech',
                'priority' => 'featured',
                'tags' => ['تكنولوجيا', 'إنترنت', 'اليمن', 'تعليم'],
                'reading_time' => 6,
            ],
            [
                'category' => 'تكنولوجيا',
                'title' => 'الطاقة الشمسية والإنترنت: ثنائي جديد لإنقاذ الخدمات الريفية',
                'title_en' => 'Solar energy and internet can rescue rural services',
                'subtitle' => 'حلول صغيرة قد تربط المدرسة والمركز الصحي بالعالم',
                'excerpt' => 'في القرى البعيدة، لا معنى للحاسوب أو الراوتر من دون طاقة مستقرة تشغلهما.',
                'lead' => 'تظهر تجربة مشاريع الطاقة الشمسية في اليمن أن التقنية لا تنجح وحدها. فالإنترنت يحتاج كهرباء، والمركز الصحي يحتاج ثلاجة لقاحات، والمدرسة تحتاج إضاءة وشحن أجهزة. لذلك تبدو الطاقة الشمسية مدخلا عمليا لأي تحول رقمي في الضالع.',
                'points' => [
                    'المنظومات الصغيرة يمكن أن تشغل نقطة إنترنت مدرسية أو غرفة حاسوب بسيطة.',
                    'المراكز الصحية تحتاج طاقة مستقرة لحفظ الأدوية واللقاحات وتشغيل الاتصال الإداري.',
                    'الصيانة والتدريب المحلي أهم من شراء الأجهزة؛ فالمنظومة التي لا تجد من يصلحها ستتعطل سريعا.',
                    'دمج مشاريع الطاقة بالمياه والتعليم والصحة يعطي أثرا أكبر من تنفيذ كل قطاع منفصلا.',
                ],
                'closing' => 'التحول الرقمي في الريف يبدأ بلوح شمسي، وفني محلي، وخطة تشغيل واضحة.',
                'source_name' => 'البنك الدولي - الطاقة والتنمية',
                'source_url' => 'https://www.worldbank.org/en/topic/energy',
                'published_at' => '2026-01-28 11:00:00',
                'location' => 'الضالع',
                'image' => 'solar',
                'priority' => 'normal',
                'tags' => ['تكنولوجيا', 'طاقة شمسية', 'ريف', 'خدمات'],
                'reading_time' => 6,
            ],
            [
                'category' => 'تكنولوجيا',
                'title' => 'التحقق من الأخبار أصبح مهارة يومية في مجموعات الواتساب',
                'title_en' => 'News verification becomes a daily skill in WhatsApp groups',
                'subtitle' => 'منصات التواصل تنقل الخبر بسرعة لكنها تنقل الشائعة أسرع',
                'excerpt' => 'في بيئة الأخبار العاجلة، يحتاج القارئ إلى أدوات بسيطة للتأكد قبل المشاركة.',
                'lead' => 'مع اعتماد الناس على واتساب وفيسبوك للحصول على الأخبار المحلية، أصبحت الشائعة قادرة على الوصول إلى آلاف المستخدمين قبل صدور أي توضيح رسمي. لذلك لم يعد التحقق من الخبر عملا صحفيا فقط، بل مهارة يومية لكل قارئ.',
                'points' => [
                    'قبل مشاركة أي خبر عاجل، يجب البحث عن مصدره الأول وتاريخ نشره ومكان وقوعه.',
                    'الصور القديمة تعود كثيرا في أزمات السيول والحوادث، ويمكن كشفها بالبحث العكسي.',
                    'الخبر الذي يطلب إثارة الغضب فورا من دون تفاصيل أو مصدر يحتاج توقفا مضاعفا.',
                    'المؤسسات المحلية مطالبة بالنشر السريع والواضح حتى لا تملأ الشائعة فراغ المعلومة.',
                ],
                'closing' => 'سرعة النشر لا تعوض دقة المعلومة؛ والخبر الخاطئ قد يضر أسرة أو يربك مدينة كاملة.',
                'source_name' => 'UNESCO media and information literacy',
                'source_url' => 'https://www.unesco.org/en/media-information-literacy',
                'published_at' => '2026-02-22 14:20:00',
                'location' => 'اليمن',
                'image' => 'tech',
                'priority' => 'normal',
                'tags' => ['تكنولوجيا', 'إعلام', 'تحقق', 'واتساب'],
                'reading_time' => 5,
            ],
            [
                'category' => 'ثقافة-وفن',
                'title' => 'التراث اليمني غير المادي يواجه اختبار الحفظ وسط النزوح وتغير الأجيال',
                'title_en' => 'Yemeni intangible heritage faces preservation test amid displacement',
                'subtitle' => 'الغناء والحرف والعمارة الشعبية ذاكرة تحتاج توثيقا',
                'excerpt' => 'الحفاظ على التراث لا يعني العودة إلى الماضي، بل حماية ذاكرة المجتمع من الانقطاع.',
                'lead' => 'تدرج اليونسكو عناصر من التراث اليمني ضمن قوائم التراث الثقافي غير المادي، وتلفت الحرب والنزوح الانتباه إلى خطر انقطاع السلاسل التي تنقل الحرفة والغناء والعادات من جيل إلى آخر. في الضالع، يمتلك المجتمع ذاكرة محلية جديرة بالتوثيق.',
                'points' => [
                    'الحكايات الشعبية والأهازيج والمناسبات الزراعية تحمل تاريخا غير مكتوب للمديريات.',
                    'نزوح الأسر يقطع علاقة الأطفال بمكان الذاكرة، فيحتاج التراث إلى تسجيل صوتي وبصري منظم.',
                    'المدارس والمراكز الثقافية يمكن أن تستضيف أيام تراثية صغيرة تربط الأجيال.',
                    'توثيق التراث المحلي لا يقل أهمية عن تغطية الأخبار اليومية لأنه يحفظ هوية المكان.',
                ],
                'closing' => 'الثقافة في الضالع ليست فعالية جانبية؛ إنها أرشيف المجتمع وصوته الطويل.',
                'source_name' => 'اليونسكو - التراث الثقافي غير المادي',
                'source_url' => 'https://ich.unesco.org/en/state/yemen-YE',
                'published_at' => '2026-03-02 09:30:00',
                'location' => 'اليمن',
                'image' => 'culture',
                'priority' => 'featured',
                'tags' => ['ثقافة', 'تراث', 'اليمن', 'الضالع'],
                'reading_time' => 6,
            ],
            [
                'category' => 'ثقافة-وفن',
                'title' => 'الفن الشعبي في الأعراس والمواسم الزراعية يحفظ ذاكرة قرى الضالع',
                'title_en' => 'Folk art in weddings and agricultural seasons preserves Dhale memory',
                'subtitle' => 'الغناء والرقص الشعبيان وثيقة اجتماعية لا مجرد احتفال',
                'excerpt' => 'الأهازيج الشعبية تروي تاريخ القرى وعلاقتها بالأرض والمطر والهجرة.',
                'lead' => 'في قرى الضالع، تحمل المناسبات الاجتماعية والزراعية أنماطا فنية بسيطة لكنها عميقة الدلالة. الأغنية التي تردد في موسم أو عرس ليست ترفيها فقط، بل سجل اجتماعي عن الأرض والغربة والعلاقات بين الناس.',
                'points' => [
                    'توثيق الكلمات والألحان يساعد على حفظ تنوع اللهجات والصور الشعرية المحلية.',
                    'الفن الشعبي يمكن أن يكون مادة تعليمية وسياحية إذا عرض باحترام وبلا تشويه.',
                    'المبادرات الشبابية قادرة على تسجيل كبار السن وجمع الروايات قبل فقدانها.',
                    'الصحافة المحلية تستطيع فتح نافذة شهرية للذاكرة الشعبية في كل مديرية.',
                ],
                'closing' => 'كل أغنية قديمة تحمل خبرا من زمن آخر، ومهمة الإعلام أن يصغي لها قبل أن تختفي.',
                'source_name' => 'ملف اليونسكو للثقافة في اليمن',
                'source_url' => 'https://www.unesco.org/en/fieldoffice/doha/yemen',
                'published_at' => '2026-01-19 13:00:00',
                'location' => 'الضالع',
                'image' => 'culture',
                'priority' => 'normal',
                'tags' => ['ثقافة', 'فن شعبي', 'تراث', 'قرى'],
                'reading_time' => 5,
            ],
            [
                'category' => 'ثقافة-وفن',
                'title' => 'المكتبات الصغيرة يمكن أن تعيد القراءة إلى أحياء الضالع',
                'title_en' => 'Small libraries can bring reading back to Dhale neighborhoods',
                'subtitle' => 'مبادرات منخفضة الكلفة تصنع أثرا ثقافيا طويل المدى',
                'excerpt' => 'رف كتب في مدرسة أو مركز شبابي قد يكون بداية لحياة ثقافية أوسع.',
                'lead' => 'تحتاج الحياة الثقافية في المدن الصغيرة إلى مبادرات قريبة من الناس. المكتبة المصغرة في مدرسة أو مسجد أو مركز شبابي لا تحتاج مبنى كبيرا، لكنها تحتاج إدارة وكتبا مناسبة وأنشطة تجعل القراءة عادة اجتماعية.',
                'points' => [
                    'القراءة تعوض جزئيا ضعف الأنشطة الثقافية الرسمية وتفتح أفقا للأطفال والشباب.',
                    'يمكن جمع الكتب بالتبرع ثم تنظيمها وفق أعمار القراء واهتماماتهم.',
                    'جلسات قراءة شهرية أو مسابقات تلخيص قد تحول المكتبة إلى مساحة حوار.',
                    'ربط المكتبات بالإنترنت والطاقة الشمسية يمنحها دورا تعليميا أوسع.',
                ],
                'closing' => 'الثقافة تبدأ أحيانا من رف صغير، ويد أمينة، وطفل يجد كتابه الأول.',
                'source_name' => 'UNESCO education and culture resources',
                'source_url' => 'https://www.unesco.org/en/education',
                'published_at' => '2026-02-08 10:10:00',
                'location' => 'الضالع',
                'image' => 'education',
                'priority' => 'normal',
                'tags' => ['ثقافة', 'قراءة', 'تعليم', 'شباب'],
                'reading_time' => 5,
            ],
            [
                'category' => 'تحقيقات',
                'title' => 'تحقيق بيانات: لماذا تتكرر أزمة المياه في الضالع رغم تعدد المشاريع؟',
                'title_en' => 'Data investigation why water crises repeat in Dhale despite projects',
                'subtitle' => 'المشكلة بين التمويل والتشغيل والصيانة والحوكمة',
                'excerpt' => 'تعدد مشاريع المياه لا يعني انتهاء الأزمة إذا غابت الصيانة والشفافية وموازنات التشغيل.',
                'lead' => 'تكشف متابعة أخبار المياه في الضالع عن مفارقة واضحة: مشاريع جديدة أو معاد تشغيلها تظهر كل عام، لكن شكاوى العطش لا تختفي. هذا التحقيق التحليلي يقرأ الأزمة من زاوية دورة المشروع الكاملة لا من صورة الافتتاح.',
                'points' => [
                    'المشروع يبدأ غالبا بتمويل إنشائي، بينما تبقى كلفة التشغيل والصيانة أقل وضوحا بعد التسليم.',
                    'غياب نشر بيانات المستفيدين وساعات الضخ والتعرفة يضعف ثقة المجتمع ويصعب المحاسبة.',
                    'الطاقة هي عنق الزجاجة؛ أي مشروع ماء بلا حل كهرباء أو شمسية مستقر سيظل معرضا للتوقف.',
                    'تحتاج كل مديرية إلى لوحة متابعة عامة: البئر، الخزان، الشبكة، الأعطال، الإيرادات، والمصروفات.',
                ],
                'closing' => 'الخلاصة: أزمة المياه ليست نقص مشاريع فقط؛ إنها نقص إدارة مستمرة بعد المشروع.',
                'source_name' => 'تحليل الضالع أونلاين استنادا إلى مصادر محلية وبيانات UNICEF WASH',
                'source_url' => 'https://www.unicef.org/yemen/water-sanitation-and-hygiene',
                'published_at' => '2026-04-10 08:00:00',
                'location' => 'الضالع',
                'image' => 'water',
                'priority' => 'editors_pick',
                'tags' => ['تحقيقات', 'المياه', 'الضالع', 'حوكمة'],
                'reading_time' => 8,
            ],
            [
                'category' => 'تحقيقات',
                'title' => 'تحقيق: النازحون في الضالع بين المأوى المؤقت وغياب فرص الدخل',
                'title_en' => 'Investigation displaced families in Dhale between shelter and income gaps',
                'subtitle' => 'المساعدة الطارئة لا تكفي لبناء استقرار طويل',
                'excerpt' => 'قصص النزوح في الضالع تكشف أن السقف مهم، لكن الدخل والخدمة والحماية لا تقل أهمية.',
                'lead' => 'تظهر تقارير إنسانية عن تدخلات في الضالع أن الأسر النازحة تحتاج أكثر من استجابة طارئة. فالمأوى يحمي من المطر والبرد، لكنه لا يحل مشكلة الدخل، ولا يضمن تعليم الأطفال، ولا يعالج أثر النزوح النفسي والاجتماعي.',
                'points' => [
                    'تتكرر فجوة الانتقال من المساعدة العاجلة إلى التعافي؛ تحصل الأسرة على مأوى ثم تبقى بلا مصدر دخل.',
                    'النازحون في المناطق المضيفة يشاركون المجتمع المحلي نفس الخدمات المحدودة، ما قد يخلق ضغطا إضافيا.',
                    'المشاريع الأفضل هي التي تربط المأوى بالتدريب والدخل والمياه والحماية.',
                    'التغطية الصحفية يجب أن تحفظ كرامة النازحين وتعرض احتياجاتهم بلا استغلال بصري لمعاناتهم.',
                ],
                'closing' => 'النزوح ليس حدثا عابرا في حياة الأسرة؛ إنه مسار طويل يحتاج سياسة محلية واضحة لا حملة موسمية.',
                'source_name' => 'ACTED Yemen',
                'source_url' => 'https://www.acted.org/en/human-interest-story-restoring-hope-and-dignity-through-cash-assistance-in-al-dhalee/',
                'published_at' => '2026-01-12 09:00:00',
                'location' => 'الضالع',
                'image' => 'humanitarian',
                'priority' => 'featured',
                'tags' => ['تحقيقات', 'نزوح', 'الضالع', 'تعافي'],
                'reading_time' => 8,
            ],
            [
                'category' => 'تحقيقات',
                'title' => 'تحقيق: الغلاء في جنوب اليمن يبدأ من العملة ولا ينتهي عند السوق',
                'title_en' => 'Investigation prices in southern Yemen start with currency but do not end in markets',
                'subtitle' => 'الأسرة تدفع ثمن الانقسام النقدي والنقل وضعف الدخل',
                'excerpt' => 'ارتفاع الأسعار ليس نتيجة عامل واحد؛ العملة والنقل والدخل والرقابة كلها تدخل في الفاتورة.',
                'lead' => 'عندما ترتفع الأسعار في الضالع، يبحث الناس غالبا عن السبب في سعر الصرف. لكن التحقيق في سلسلة الكلفة يكشف عوامل أخرى: النقل، احتكار بعض السلع، ضعف الدخل، وتذبذب الإمداد. كل عامل يضيف طبقة جديدة إلى سعر الوجبة اليومية.',
                'points' => [
                    'العملة تحدد كلفة الاستيراد، لكن الطريق يحدد كلفة وصول السلعة إلى القرية.',
                    'غياب قوائم أسعار معلنة يترك المستهلك أمام تفاوت كبير بين سوق وآخر.',
                    'الأسر ذات الدخل اليومي تتضرر أكثر لأنها لا تستطيع شراء كميات كبيرة عند انخفاض السعر.',
                    'الحل المحلي يبدأ برصد أسعار شفاف، وتشجيع الأسواق الشعبية، وحماية المنافسة.',
                ],
                'closing' => 'لا يمكن ضبط السوق من دون معلومة يومية مفتوحة، ولا يمكن حماية الأسرة من دون فهم كامل لسلسلة السعر.',
                'source_name' => 'البنك الدولي وبرنامج الأغذية العالمي',
                'source_url' => 'https://www.wfp.org/countries/yemen',
                'published_at' => '2026-03-29 09:25:00',
                'location' => 'جنوب اليمن',
                'image' => 'economy',
                'priority' => 'normal',
                'tags' => ['تحقيقات', 'اقتصاد', 'أسعار الصرف', 'غذاء'],
                'reading_time' => 8,
            ],
            [
                'category' => 'تقارير-مصورة',
                'title' => 'تقرير مصور: من خيمة إلى مأوى أكثر أمانا في الضالع',
                'title_en' => 'Photo report from tent to safer shelter in Dhale',
                'subtitle' => 'قصة تعاف إنساني توثق معنى السكن الآمن',
                'excerpt' => 'تدخلات المأوى في الضالع تكشف أن الصورة الإنسانية الحقيقية تبدأ من استعادة الخصوصية والأمان.',
                'lead' => 'في تقارير إنسانية منشورة عن الضالع، لا تظهر الخيمة بوصفها صورة فقر فقط، بل بوصفها مرحلة من رحلة طويلة نحو التعافي. الانتقال إلى مأوى أكثر أمانا يعني سقفا أفضل، ومساحة أكثر خصوصية، وقدرة أعلى على حماية الأطفال.',
                'points' => [
                    'الصورة الأولى في التقرير يجب أن تكون للكرامة لا للصدمة: كيف تستعيد الأسرة نظامها اليومي؟',
                    'المأوى الجيد يقلل التعرض للأمطار والحرارة، ويمنح النساء والأطفال مساحة أكثر أمنا.',
                    'الاستجابة الإنسانية الناجحة تقيس رضا الأسرة بعد التسليم، لا عدد الخيام أو المواد الموزعة فقط.',
                    'توثيق القصص الفردية يساعد الجمهور على فهم معنى الأرقام الكبيرة للنزوح.',
                ],
                'closing' => 'الصورة القوية ليست تلك التي تعرض الألم وحده، بل التي تشرح كيف يمكن إصلاح جزء منه.',
                'source_name' => 'ACTED Yemen',
                'source_url' => 'https://www.acted.org/en/human-interest-story-restoring-hope-and-dignity-through-cash-assistance-in-al-dhalee/',
                'published_at' => '2026-01-20 08:30:00',
                'location' => 'الضالع',
                'image' => 'humanitarian',
                'priority' => 'featured',
                'tags' => ['تقارير مصورة', 'نزوح', 'مأوى', 'الضالع'],
                'reading_time' => 6,
            ],
            [
                'category' => 'تقارير-مصورة',
                'title' => 'تقرير مصور: الأمطار والسيول تكشف هشاشة الطرق الريفية في اليمن',
                'title_en' => 'Photo report rains and floods expose rural road fragility in Yemen',
                'subtitle' => 'الطريق المقطوع يعزل المدرسة والمستشفى والسوق',
                'excerpt' => 'مشاهد السيول في المحافظات الجبلية تضع صيانة الطرق ضمن أولويات الحماية لا النقل فقط.',
                'lead' => 'كل موسم أمطار يعيد السؤال نفسه في القرى الجبلية: ماذا يحدث عندما يقطع السيل الطريق الوحيد؟ التقرير المصور هنا لا يتوقف عند مشهد الماء، بل يتابع أثره على حركة المرضى والطلاب والسلع.',
                'points' => [
                    'الصور الجوية أو الميدانية للطرق قبل وبعد السيل تساعد على تحديد نقاط الخطر المتكررة.',
                    'الجسور الصغيرة ومصارف المياه قد تكون أكثر أثرا من مشاريع طرق كبيرة إذا وضعت في المكان الصحيح.',
                    'تعطل الطريق يرفع الأسعار ويؤخر الإسعاف ويزيد عزلة الأسر.',
                    'نشر خريطة مجتمعية للنقاط الخطرة يمكن أن يساعد السلطات والمنظمات على ترتيب التدخلات.',
                ],
                'closing' => 'الصورة هنا وثيقة تخطيط: أين ينقطع الطريق؟ ومن يتضرر أولا؟ وكم يكلف الإصلاح؟',
                'source_name' => 'الاتحاد الدولي لجمعيات الصليب والهلال الأحمر',
                'source_url' => 'https://www.ifrc.org/emergency/yemen-floods',
                'published_at' => '2026-02-16 07:50:00',
                'location' => 'اليمن',
                'image' => 'roads',
                'priority' => 'normal',
                'tags' => ['تقارير مصورة', 'سيول', 'طرقات', 'ريف'],
                'reading_time' => 6,
            ],
            [
                'category' => 'تقارير-مصورة',
                'title' => 'تقرير مصور: سوق الضالع بين الغلاء وحركة الناس اليومية',
                'title_en' => 'Photo report Dhale market between high prices and daily movement',
                'subtitle' => 'السوق مرآة الاقتصاد المحلي أكثر من أي بيان رسمي',
                'excerpt' => 'من رفوف الدقيق إلى بسطات الخضار، يروي السوق قصة الدخل والأسعار والنقل.',
                'lead' => 'السوق هو المكان الذي تتحول فيه التقارير الاقتصادية إلى واقع مرئي. في سوق الضالع، يمكن للصورة أن تلتقط ما لا تقوله الجداول: وجوه المشترين، أحجام الأكياس، حركة النقل، وتفاوت الأسعار بين الصباح والمساء.',
                'points' => [
                    'اللقطات الواسعة تكشف الحركة العامة، بينما تظهر اللقطات القريبة تفاصيل الأسعار والكميات.',
                    'سؤال الباعة عن كلفة النقل يشرح لماذا يختلف السعر بين المدينة والقرية.',
                    'تكرار التصوير من نفس الزاوية شهريا يصنع أرشيفا اقتصاديا محليا مفيدا.',
                    'حماية خصوصية الناس واجبة؛ السوق ليس مسرحا للتصوير العشوائي بل مساحة رزق.',
                ],
                'closing' => 'الصورة الاقتصادية الجيدة لا تبحث عن الإثارة، بل عن الدليل: سعر، كمية، حركة، ووجهة نظر الناس.',
                'source_name' => 'برنامج الأغذية العالمي - اليمن',
                'source_url' => 'https://www.wfp.org/countries/yemen',
                'published_at' => '2026-03-07 08:10:00',
                'location' => 'الضالع',
                'image' => 'economy',
                'priority' => 'normal',
                'tags' => ['تقارير مصورة', 'اقتصاد', 'سوق', 'الضالع'],
                'reading_time' => 5,
            ],
            [
                'category' => 'منوعات',
                'title' => 'مدرجات الأزارق الزراعية تعود للون الأخضر مع مواسم المطر',
                'title_en' => 'Al Azariq terraces return green during rainy seasons',
                'subtitle' => 'الطبيعة تقدم وجها آخر للضالع بعيدا عن أخبار الأزمات',
                'excerpt' => 'مشاهد المدرجات الزراعية تذكر بأن الضالع ليست أخبار خدمات وسياسة فقط، بل أرض وجبال ومواسم.',
                'lead' => 'حين تهطل الأمطار على الأزارق والمناطق الجبلية، تعود المدرجات الزراعية لتكشف وجها مختلفا للضالع. هذا الوجه الطبيعي لا يلغي أزمات المياه والطرق، لكنه يذكر بأن المحافظة تمتلك مقومات حياة وجمال واقتصاد ريفي.',
                'points' => [
                    'المدرجات الزراعية تحتاج صيانة تقليدية حتى لا تنهار مع السيول.',
                    'تصوير المواسم الزراعية يمكن أن يدعم الوعي البيئي والسياحة الداخلية الصغيرة.',
                    'الحفاظ على البذور المحلية والمعرفة الزراعية جزء من حماية التراث.',
                    'تنظيم رحلات مدرسية قصيرة للمناطق الطبيعية قد يعزز ارتباط الطلاب بأرضهم.',
                ],
                'closing' => 'المنوعات ليست هروبا من الواقع؛ أحيانا هي نافذة لرؤية ما يستحق الحماية في الواقع.',
                'source_name' => 'ملف تحريري عن الريف اليمني والبيئة',
                'source_url' => 'https://www.fao.org/yemen',
                'published_at' => '2026-02-27 15:00:00',
                'location' => 'الأزارق - الضالع',
                'image' => 'default',
                'priority' => 'normal',
                'tags' => ['منوعات', 'الأزارق', 'زراعة', 'بيئة'],
                'reading_time' => 5,
            ],
            [
                'category' => 'منوعات',
                'title' => 'كيف يقرأ أبناء الضالع أخبار الطقس قبل موسم الأمطار؟',
                'title_en' => 'How Dhale residents read weather news before rainy season',
                'subtitle' => 'المعلومة الجوية قد تتحول إلى أداة حماية يومية',
                'excerpt' => 'متابعة الطقس لم تعد اهتماما عابرا، بل جزءا من الاستعداد للسيول والطرق والزراعة.',
                'lead' => 'مع تكرار موجات المطر والسيول، أصبحت متابعة الطقس في الضالع عادة يومية لكثير من السائقين والمزارعين والأسر. الخبر الجوي لم يعد مجرد درجة حرارة؛ إنه قرار سفر، وري، وحماية منزل، وتأجيل رحلة.',
                'points' => [
                    'المزارع يحتاج توقع المطر ليحدد مواعيد الحراثة والبذر وحماية المدرجات.',
                    'السائق يحتاج معرفة التحذيرات قبل عبور طرق جبلية أو أودية.',
                    'الأسرة تحتاج رسائل مبسطة عن حماية الأطفال والممتلكات أثناء السيول.',
                    'نشر تنبيهات محلية بلغة واضحة أفضل من الاكتفاء بخرائط فنية لا يفهمها الجميع.',
                ],
                'closing' => 'الطقس خبر حياة يومية في محافظة جبلية؛ والتنبؤ الجيد قد يمنع خسارة.',
                'source_name' => 'منظمة الأرصاد العالمية',
                'source_url' => 'https://wmo.int/',
                'published_at' => '2026-03-11 07:30:00',
                'location' => 'الضالع',
                'image' => 'default',
                'priority' => 'normal',
                'tags' => ['منوعات', 'طقس', 'سيول', 'زراعة'],
                'reading_time' => 5,
            ],
            [
                'category' => 'منوعات',
                'title' => 'دفتر صغير لإدارة مصروف البيت في زمن الغلاء',
                'title_en' => 'A small notebook to manage household spending during inflation',
                'subtitle' => 'نصائح عملية من واقع الأسر لا من كتب الاقتصاد',
                'excerpt' => 'في ظل تذبذب الأسعار، يساعد تسجيل المصروفات على رؤية أين يذهب الدخل القليل.',
                'lead' => 'لا يحتاج ضبط مصروف البيت إلى تطبيق معقد دائما. في ظروف الغلاء، قد يكون دفتر صغير وقلم بداية عملية لفهم الإنفاق اليومي، خصوصا عندما تتغير الأسعار بسرعة ويضيع الدخل بين النقل والغذاء والدواء.',
                'points' => [
                    'تسجيل المصروفات لمدة أسبوع يكشف البنود التي يمكن تقليلها أو شراؤها جماعيا.',
                    'شراء السلع الأساسية وفق قائمة يقلل الاندفاع أمام عروض غير ضرورية.',
                    'تقاسم النقل أو الشراء بين جيران قد يخفض بعض الكلفة في القرى البعيدة.',
                    'المهم ألا يتحول الاقتصاد المنزلي إلى لوم للأسر؛ المشكلة أكبر من البيت لكنها تحتاج أدوات صغيرة للتعامل معها.',
                ],
                'closing' => 'المعرفة اليومية بالمصروف لا تحل الغلاء، لكنها تمنح الأسرة قدرة أفضل على القرار.',
                'source_name' => 'إرشادات مالية أسرية من البنك الدولي',
                'source_url' => 'https://www.worldbank.org/en/topic/financialinclusion',
                'published_at' => '2026-03-21 16:00:00',
                'location' => 'اليمن',
                'image' => 'economy',
                'priority' => 'normal',
                'tags' => ['منوعات', 'اقتصاد منزلي', 'أسعار', 'أسرة'],
                'reading_time' => 5,
            ],
        ];
    }

    private function articleItems(): array
    {
        return [
            [
                'category' => 'اقتصاد',
                'title' => 'لماذا لا يشعر المواطن بتحسن الاقتصاد حتى عندما تهدأ الأسعار؟',
                'title_en' => 'Why citizens do not feel economic relief when prices calm',
                'excerpt' => 'هدوء السعر لا يكفي إذا كان الدخل قد تآكل والديون المنزلية تراكمت.',
                'lead' => 'يقيس المواطن الاقتصاد من جيبه لا من المؤشرات العامة. لذلك قد تعلن الأسواق استقرارا نسبيا، بينما لا تشعر الأسرة بتحسن حقيقي لأن الدخل لم يتغير والديون السابقة ما زالت مفتوحة.',
                'points' => [
                    'المؤشر الأهم للأسرة هو عدد الأيام التي يكفيها الدخل، لا سعر سلعة واحدة.',
                    'التحسن الحقيقي يحتاج دخلا مستقرا وخدمات أقل كلفة وفرص عمل قريبة.',
                    'على الإعلام الاقتصادي المحلي أن يشرح الأرقام بلغة البيت والسوق لا بلغة النشرات فقط.',
                ],
                'closing' => 'الاقتصاد العادل هو الذي يترجم الاستقرار إلى قدرة شراء، لا إلى عناوين مطمئنة فقط.',
                'source_name' => 'البنك الدولي',
                'source_url' => 'https://www.worldbank.org/en/country/yemen/publication/yemen-economic-monitor',
                'published_at' => '2026-05-23 09:00:00',
                'image' => 'economy',
                'type' => 'analysis',
                'priority' => 'featured',
                'tags' => ['مقالات', 'اقتصاد', 'دخل', 'اليمن'],
                'reading_time' => 7,
            ],
            [
                'category' => 'مجتمع',
                'title' => 'المدرسة في الريف اليمني: أكثر من فصل وسبورة',
                'title_en' => 'The rural Yemeni school is more than a classroom',
                'excerpt' => 'المدرسة الريفية هي نقطة ماء وحماية وصحة ومعرفة في وقت واحد.',
                'lead' => 'عندما نتحدث عن التعليم في الريف، فإننا لا نتحدث عن منهج فقط. المدرسة في القرية مساحة حماية، ونقطة لقاء، ومكان يمكن أن تصل منه رسائل الصحة والمياه والتوعية.',
                'points' => [
                    'أي خطة تعليمية بلا ماء وصرف صحي ستبقى ناقصة.',
                    'المعلم المحلي يحتاج دعما اجتماعيا وماديا حتى يستمر في المدرسة.',
                    'عودة الطلاب المنقطعين تبدأ من فهم أسباب الانقطاع لا من الوعظ العام.',
                ],
                'closing' => 'إصلاح المدرسة الريفية هو إصلاح للمجتمع كله، لأنه يمس الطفل والأسرة والمستقبل.',
                'source_name' => 'اليونيسف',
                'source_url' => 'https://www.unicef.org/yemen/education',
                'published_at' => '2026-03-08 10:00:00',
                'image' => 'education',
                'type' => 'column',
                'priority' => 'normal',
                'tags' => ['مقالات', 'تعليم', 'ريف', 'مجتمع'],
                'reading_time' => 6,
            ],
            [
                'category' => 'سياسة',
                'title' => 'بناء الثقة يبدأ من ملف إنساني صغير وينتهي بمسار سياسي كبير',
                'title_en' => 'Confidence building begins with humanitarian files',
                'excerpt' => 'في اليمن، قد يكون الإفراج عن محتجز بداية أعمق من بيان سياسي طويل.',
                'lead' => 'ليست إجراءات بناء الثقة تفصيلا جانبيا في الحروب الطويلة. فعندما يعود محتجز إلى أسرته، تتغير نظرة المجتمع إلى إمكانية الحل، ولو قليلا.',
                'points' => [
                    'كل اتفاق إنساني ناجح يفتح مساحة لاختبار الالتزام.',
                    'فشل التنفيذ يضاعف الإحباط ويجعل أي إعلان جديد أقل إقناعا.',
                    'المجتمعات المحلية تحتاج أن ترى نتائج ملموسة حتى تؤمن بالمسار السياسي.',
                ],
                'closing' => 'السياسة التي لا تمر عبر وجع الناس لن تكسب ثقتهم.',
                'source_name' => 'الأمم المتحدة',
                'source_url' => 'https://www.ungeneva.org/en/news-media/news/2026/05/118661/yemen-parties-agree-under-un-mediation-release-1600-detainees',
                'published_at' => '2026-05-15 12:00:00',
                'image' => 'humanitarian',
                'type' => 'opinion',
                'priority' => 'editors_pick',
                'tags' => ['مقالات', 'سياسة', 'السلام', 'اليمن'],
                'reading_time' => 6,
            ],
            [
                'category' => 'تكنولوجيا',
                'title' => 'قبل أن نطلب حكومة إلكترونية يجب أن نسأل عن الكهرباء والإنترنت',
                'title_en' => 'Before e government ask about power and internet',
                'excerpt' => 'الخدمة الرقمية تبدأ من حق الاتصال لا من تصميم المنصة.',
                'lead' => 'تبدو الخدمات الرقمية جذابة في العناوين، لكنها قد تتحول إلى عبء جديد إذا افترضت أن كل مواطن يملك هاتفا حديثا وإنترنت مستقرا وكهرباء للشحن.',
                'points' => [
                    'التصميم الجيد يراعي الهاتف الضعيف والاتصال البطيء واللغة البسيطة.',
                    'مراكز خدمة صغيرة قد تكون جسرا بين المواطن والمنصة الرقمية.',
                    'الطاقة الشمسية في المدارس والمراكز الصحية قد تكون أساس التحول الرقمي المحلي.',
                ],
                'closing' => 'الرقمنة العادلة لا تبدأ من التطبيق، بل من قدرة الناس على الوصول إليه.',
                'source_name' => 'GSMA',
                'source_url' => 'https://www.mobileconnectivityindex.com/',
                'published_at' => '2026-02-18 11:00:00',
                'image' => 'tech',
                'type' => 'analysis',
                'priority' => 'featured',
                'tags' => ['مقالات', 'تكنولوجيا', 'إنترنت', 'خدمات'],
                'reading_time' => 7,
            ],
            [
                'category' => 'ثقافة-وفن',
                'title' => 'ذاكرة القرية اليمنية ليست في الكتب وحدها',
                'title_en' => 'Yemeni village memory is not only in books',
                'excerpt' => 'الأغاني والحكايات والأسماء القديمة أرشيف يجب أن يلتفت إليه الإعلام المحلي.',
                'lead' => 'يحمل كبار السن في القرى اليمنية أرشيفا شفهيا لا يوجد في أي مكتبة: أسماء عيون الماء، طرق القوافل، قصص المواسم، وأهازيج الحصاد.',
                'points' => [
                    'التوثيق الصوتي والبصري البسيط قد يحفظ مادة ثقافية لا تعوض.',
                    'الشباب قادرون على تحويل الهاتف إلى أداة أرشفة مسؤولة.',
                    'التراث لا يجب أن يعرض كديكور، بل كمعرفة حية مرتبطة بالأرض والناس.',
                ],
                'closing' => 'حين يموت راو كبير بلا تسجيل، تفقد القرية جزءا من ذاكرتها.',
                'source_name' => 'اليونسكو',
                'source_url' => 'https://ich.unesco.org/en/state/yemen-YE',
                'published_at' => '2026-01-22 14:00:00',
                'image' => 'culture',
                'type' => 'blog',
                'priority' => 'normal',
                'tags' => ['مقالات', 'ثقافة', 'تراث', 'قرية'],
                'reading_time' => 6,
            ],
        ];
    }

    private function reportItems(): array
    {
        return [
            [
                'category' => 'تقارير-مصورة',
                'title' => 'ملف خاص: خريطة الاحتياجات الخدمية في مديريات الضالع',
                'title_en' => 'Special file service needs map in Dhale districts',
                'excerpt' => 'تجميع تحريري لأبرز ملفات المياه والتعليم والصحة والطرق كما تظهر في التقارير المحلية والدولية.',
                'lead' => 'يجمع هذا الملف مؤشرات متفرقة عن الخدمات في الضالع، ويعيد ترتيبها حسب أثرها اليومي على المواطن.',
                'points' => [
                    'المياه والطاقة تظهران كملفين متداخلين في أغلب المديريات.',
                    'الصحة والتعليم يتأثران مباشرة بانقطاع الكهرباء وشح المياه وبعد الطرق.',
                    'الطرق الريفية هي البنية التي تحدد كلفة الوصول إلى كل خدمة أخرى.',
                ],
                'closing' => 'الهدف من الملف هو تحويل الأخبار المتفرقة إلى خريطة أولويات قابلة للمتابعة.',
                'source_name' => 'تجميع تحريري من مصادر أممية ومحلية',
                'source_url' => 'https://reliefweb.int/country/yem',
                'published_at' => '2026-04-01 08:00:00',
                'location' => 'الضالع',
                'image' => 'default',
                'type' => 'written',
                'priority' => 'featured',
                'reading_time' => 8,
            ],
            [
                'category' => 'تحقيقات',
                'title' => 'تقرير معمق: أثر النزوح على الخدمات في المجتمعات المضيفة',
                'title_en' => 'In depth report displacement impact on host communities',
                'excerpt' => 'النزوح يضيف ضغطا على الماء والمدرسة والمركز الصحي، لكنه يكشف أيضا تضامن المجتمعات المحلية.',
                'lead' => 'يتناول هذا التقرير أثر النزوح في المناطق المضيفة من زاوية الخدمات لا من زاوية المخيمات وحدها.',
                'points' => [
                    'ارتفاع عدد السكان الفعليين يضغط على شبكات مياه صممت لعدد أقل.',
                    'المدارس تستوعب طلابا إضافيين من دون توسعة كافية في الفصول والمواد.',
                    'المراكز الصحية تواجه طلبا أعلى مع تمويل محدود وأدوية غير كافية.',
                ],
                'closing' => 'الاستجابة الجيدة يجب أن تدعم النازح والمضيف معا حتى لا يتحول التضامن إلى عبء دائم.',
                'source_name' => 'IOM Yemen DTM',
                'source_url' => 'https://dtm.iom.int/yemen',
                'published_at' => '2026-02-14 09:30:00',
                'location' => 'اليمن',
                'image' => 'humanitarian',
                'type' => 'written',
                'priority' => 'normal',
                'reading_time' => 7,
            ],
            [
                'category' => 'اقتصاد',
                'title' => 'تقرير اقتصادي: السلة الغذائية بين سعر الصرف والنقل',
                'title_en' => 'Economic report food basket between exchange rate and transport',
                'excerpt' => 'قراءة مبسطة في سلسلة كلفة الغذاء من الميناء إلى القرية.',
                'lead' => 'لا يصل ارتفاع سعر الغذاء إلى المستهلك عبر العملة وحدها، بل عبر سلسلة طويلة من النقل والتخزين والمخاطر.',
                'points' => [
                    'كل نقطة نقل تضيف كلفة وقد تضيف فاقدا أو رسوما غير مباشرة.',
                    'المناطق الجبلية تدفع غالبا كلفة أعلى من مراكز المدن.',
                    'نشر متوسطات أسعار أسبوعية يساعد الأسر والتجار على اتخاذ قرارات أفضل.',
                ],
                'closing' => 'قراءة السوق المحلي يجب أن تبدأ من السلة لا من سعر صرف الدولار فقط.',
                'source_name' => 'WFP Yemen',
                'source_url' => 'https://www.wfp.org/countries/yemen',
                'published_at' => '2026-03-18 10:30:00',
                'location' => 'اليمن',
                'image' => 'economy',
                'type' => 'written',
                'priority' => 'normal',
                'reading_time' => 7,
            ],
        ];
    }
}
