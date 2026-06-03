<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مدير الموقع
        $admin = User::create([
            'name' => 'أحمد محمد الضالعي',
            'username' => 'admin',
            'email' => 'admin@aldhalea-online.com',
            'password' => Hash::make('password123'),
            'phone' => '777000001',
            'role' => 'admin',
            'bio' => 'مدير تحرير موقع الضالع أونلاين - صحفي وباحث يمني',
            'location' => 'الضالع',
            'is_active' => true,
            'is_verified' => true,
        ]);

        // المحررين
        $editors = [
            ['name' => 'سامية عبدالله', 'username' => 'samia_editor', 'email' => 'samia@aldhalea.com', 'location' => 'قعطبة', 'bio' => 'محررة أولى - متخصصة في الشأن السياسي'],
            ['name' => 'فهد الحميري', 'username' => 'fahd_editor', 'email' => 'fahd@aldhalea.com', 'location' => 'دمت', 'bio' => 'محرر - متخصص في الشأن الاقتصادي'],
            ['name' => 'نورة السعيدي', 'username' => 'noura_editor', 'email' => 'noura@aldhalea.com', 'location' => 'جحاف', 'bio' => 'محررة - متخصصة في الشأن الاجتماعي'],
        ];

        foreach ($editors as $editor) {
            User::create(array_merge($editor, [
                'password' => Hash::make('password123'),
                'role' => 'editor',
                'is_active' => true,
                'is_verified' => true,
            ]));
        }

        // الكتّاب
        $writers = [
            ['name' => 'د. محمد ناصر', 'username' => 'dr_nasser', 'email' => 'nasser@aldhalea.com', 'location' => 'الضالع', 'bio' => 'كاتب ومحلل سياسي - أستاذ العلوم السياسية'],
            ['name' => 'فاطمة العيسائي', 'username' => 'fatima_writer', 'email' => 'fatima@aldhalea.com', 'location' => 'الأزارق', 'bio' => 'كاتبة رأي - ناشطة حقوقية'],
            ['name' => 'خالد اليافعي', 'username' => 'khaled_writer', 'email' => 'khaled@aldhalea.com', 'location' => 'الشعيب', 'bio' => 'كاتب ومحلل اقتصادي'],
            ['name' => 'أمل الحالمي', 'username' => 'amal_writer', 'email' => 'amal@aldhalea.com', 'location' => 'الضالع', 'bio' => 'كاتبة أدبية واجتماعية'],
            ['name' => 'عبدالرحمن الميسري', 'username' => 'abdu_writer', 'email' => 'abdu@aldhalea.com', 'location' => 'الحصين', 'bio' => 'كاتب تقني - مدون'],
        ];

        foreach ($writers as $writer) {
            User::create(array_merge($writer, [
                'password' => Hash::make('password123'),
                'role' => 'writer',
                'is_active' => true,
                'is_verified' => true,
            ]));
        }

        // قراء عاديون
        $readers = ['علي أحمد', 'منى قاسم', 'حسن علي', 'مريم صالح', 'إبراهيم ناصر'];
        foreach ($readers as $i => $reader) {
            User::create([
                'name' => $reader,
                'username' => 'reader' . ($i + 1),
                'email' => 'reader' . ($i + 1) . '@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'reader',
                'location' => 'الضالع',
                'is_active' => true,
                'is_verified' => true,
            ]);
        }
    }
}
