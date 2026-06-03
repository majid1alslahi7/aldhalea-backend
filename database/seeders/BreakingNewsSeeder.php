<?php

namespace Database\Seeders;

use App\Models\BreakingNews;
use App\Models\User;
use Illuminate\Database\Seeder;

class BreakingNewsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'admin')->first();

        $breakingItems = [
            ['title' => ['ar' => 'عاجل: سيول جارفة تجتاح مديرية جحاف', 'en' => 'Breaking: Flash floods hit Jahaf district'], 'priority' => 'critical'],
            ['title' => ['ar' => 'عاجل: إعلان حالة الطوارئ في مستشفى الضالع', 'en' => 'Breaking: State of emergency declared at Dhale Hospital'], 'priority' => 'high'],
            ['title' => ['ar' => 'عاجل: انقطاع الكهرباء عن كافة مديريات المحافظة', 'en' => 'Breaking: Power outage across all governorate districts'], 'priority' => 'high'],
        ];

        foreach ($breakingItems as $item) {
            BreakingNews::create(array_merge($item, [
                'is_active' => true,
                'user_id' => $user->id,
                'starts_at' => now(),
                'ends_at' => now()->addHours(6),
            ]));
        }
    }
}
