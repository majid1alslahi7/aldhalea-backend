<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $news = News::all();
        $users = User::all();

        $comments = [
            'ممتاز.. شكراً على التغطية',
            'الله يحفظ الضالع وأهلها',
            'نتمنى المزيد من الأخبار المحلية',
            'جهود تشكرون عليها',
            'نطالب بمزيد من الاهتمام بالخدمات',
            'خبر مهم جداً.. شكراً',
            'التفاصيل غير كافية نرجو الإيضاح',
            'عمل رائع يا شباب',
            'نتمنى نقل الخبر للجهات المسؤولة',
            'الله يوفقكم',
            'مبادرة جميلة تستحق الدعم',
            'متى ستنتهي هذه الأزمة؟',
            'تحليل موفق ومعلومات دقيقة',
            'أتمنى التركيز أكثر على قضايا الشباب',
        ];

        foreach ($news->random(10) as $item) {
            foreach ($comments as $comment) {
                if (rand(0, 1)) {
                    Comment::create([
                        'content' => $comment,
                        'commentable_type' => News::class,
                        'commentable_id' => $item->id,
                        'user_id' => $users->random()->id,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'likes_count' => rand(0, 20),
                    ]);
                }
            }
        }
    }
}
