<?php

namespace Database\Seeders;

use App\Models\Interview;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['editor', 'admin'])->get();
        $categories = Category::all();

        $interviews = [
            ['interviewee_name' => 'محافظ الضالع', 'interviewee_title' => 'محافظ محافظة الضالع', 'title' => ['ar' => 'المحافظ: الضالع تشهد نهضة تنموية', 'en' => 'Governor: Dhale Witnessing Development Renaissance'], 'type' => 'text', 'location' => 'الضالع'],
            ['interviewee_name' => 'د. سالم اليافعي', 'interviewee_title' => 'رئيس جامعة الضالع', 'title' => ['ar' => 'رئيس جامعة الضالع: خطة لتطوير التعليم العالي', 'en' => 'Dhale University President: Plan for Higher Education Development'], 'type' => 'video', 'location' => 'الضالع'],
            ['interviewee_name' => 'الشيخ ناصر الحميري', 'interviewee_title' => 'شيخ مشائخ الضالع', 'title' => ['ar' => 'شيخ المشائخ: القبيلة داعمة للسلام', 'en' => 'Chief Sheikh: Tribe Supports Peace'], 'type' => 'podcast', 'location' => 'الضالع'],
            ['interviewee_name' => 'د. أمل العيسائي', 'interviewee_title' => 'ناشطة حقوقية', 'title' => ['ar' => 'ناشطة حقوقية: المرأة اليمنية تواجه تحديات كبيرة', 'en' => 'Activist: Yemeni Women Face Great Challenges'], 'type' => 'text', 'location' => 'الضالع'],
            ['interviewee_name' => 'م. خالد السعيدي', 'interviewee_title' => 'خبير اقتصادي', 'title' => ['ar' => 'خبير اقتصادي: الضالع تمتلك مقومات زراعية واعدة', 'en' => 'Economist: Dhale Has Promising Agricultural Potential'], 'type' => 'video', 'location' => 'الضالع'],
        ];

        foreach ($interviews as $item) {
            $slug = Str::slug($item['title']['ar']);
            Interview::create(array_merge($item, [
                'slug' => ['ar' => $slug, 'en' => Str::slug($item['title']['en'] ?? '')],
                'category_id' => $categories->random()->id,
                'interviewer_id' => $users->random()->id,
                'status' => 'published',
                'content' => ['ar' => 'نص المقابلة الكامل...', 'en' => 'Full interview text...'],
                'excerpt' => ['ar' => 'أهم ما جاء في المقابلة', 'en' => 'Interview highlights'],
                'views_count' => rand(150, 2500),
                'published_at' => now()->subDays(rand(0, 50)),
                'interview_date' => now()->subDays(rand(1, 50)),
                'duration' => rand(10, 90),
            ]));
        }
    }
}
