<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'admin')->first();

        $polls = [
            [
                'question' => ['ar' => 'ما أهم مشكلة تواجه محافظة الضالع حالياً؟', 'en' => 'What is the most important problem facing Dhale currently?'],
                'options' => ['انقطاع الكهرباء', 'نقص المياه', 'البطالة', 'تدهور الطرقات', 'ضعف الخدمات الصحية'],
                'is_featured' => true,
            ],
            [
                'question' => ['ar' => 'هل تؤيد فتح أسواق شعبية دائمة في مديريات الضالع؟', 'en' => 'Do you support opening permanent popular markets in Dhale districts?'],
                'options' => ['نعم بشدة', 'نعم', 'محايد', 'لا', 'لا بشدة'],
                'is_featured' => false,
            ],
            [
                'question' => ['ar' => 'أي مديرية تحتاج لأكبر قدر من الاهتمام التنموي؟', 'en' => 'Which district needs the most development attention?'],
                'options' => ['قعطبة', 'دمت', 'جحاف', 'الأزارق', 'الشعيب', 'الحصين'],
                'is_featured' => false,
            ],
            [
                'question' => ['ar' => 'ما رأيك في أداء السلطة المحلية بالضالع؟', 'en' => 'What is your opinion on the performance of the local authority in Dhale?'],
                'options' => ['ممتاز', 'جيد', 'مقبول', 'ضعيف', 'سيء جداً'],
                'is_featured' => true,
            ],
            [
                'question' => ['ar' => 'هل تتابع أخبار الضالع أونلاين يومياً؟', 'en' => 'Do you follow Dhale Online news daily?'],
                'options' => ['نعم يومياً', 'أحياناً', 'نادراً', 'لا'],
                'is_featured' => false,
            ],
        ];

        foreach ($polls as $pollData) {
            $poll = Poll::create([
                'question' => $pollData['question'],
                'status' => 'active',
                'is_featured' => $pollData['is_featured'],
                'user_id' => $user->id,
                'starts_at' => now()->subDays(rand(1, 10)),
                'ends_at' => now()->addDays(rand(10, 30)),
            ]);

            foreach ($pollData['options'] as $i => $optionText) {
                $votes = rand(10, 500);
                PollOption::create([
                    'poll_id' => $poll->id,
                    'text' => ['ar' => $optionText, 'en' => $optionText],
                    'color' => ['#1E40AF', '#059669', '#DC2626', '#EA580C', '#7C3AED', '#DB2777'][$i] ?? '#6B7280',
                    'votes_count' => $votes,
                    'order' => $i + 1,
                ]);
            }
        }
    }
}
