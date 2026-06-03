<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'الضالع أونلاين', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'أخبار عالمية شاملة وتحليلات عميقة ومقابلات حصرية وتقارير مُصوّرة', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_slogan', 'value' => 'أينما كنت نحيطك بالخبر', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_logo', 'value' => null, 'group' => 'general', 'is_public' => true],
            ['key' => 'site_favicon', 'value' => null, 'group' => 'general', 'is_public' => true],
            ['key' => 'admin_email', 'value' => 'admin@aldhalea-online.com', 'group' => 'contact', 'is_public' => false],
            ['key' => 'contact_email', 'value' => 'info@aldhalea-online.com', 'group' => 'contact', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => '00967-XXXXXXXX', 'group' => 'contact', 'is_public' => true],
            ['key' => 'address', 'value' => 'الضالع - اليمن', 'group' => 'contact', 'is_public' => true],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/aldhaleaonline', 'group' => 'social', 'is_public' => true],
            ['key' => 'twitter_url', 'value' => 'https://twitter.com/aldhaleaonline', 'group' => 'social', 'is_public' => true],
            ['key' => 'youtube_url', 'value' => 'https://youtube.com/@aldhaleaonline', 'group' => 'social', 'is_public' => true],
            ['key' => 'telegram_url', 'value' => 'https://t.me/aldhaleaonline', 'group' => 'social', 'is_public' => true],
            ['key' => 'whatsapp_url', 'value' => 'https://whatsapp.com/channel/aldhalea', 'group' => 'social', 'is_public' => true],
            ['key' => 'posts_per_page', 'value' => '15', 'group' => 'reading', 'is_public' => false],
            ['key' => 'enable_comments', 'value' => '1', 'group' => 'features', 'is_public' => false],
            ['key' => 'enable_polls', 'value' => '1', 'group' => 'features', 'is_public' => false],
            ['key' => 'enable_newsletter', 'value' => '1', 'group' => 'features', 'is_public' => false],
            ['key' => 'enable_citizen_reports', 'value' => '1', 'group' => 'features', 'is_public' => false],
            ['key' => 'google_analytics', 'value' => null, 'group' => 'analytics', 'is_public' => false],
            ['key' => 'default_language', 'value' => 'ar', 'group' => 'localization', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
