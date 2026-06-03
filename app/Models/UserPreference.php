<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id', 'preferred_categories', 'preferred_tags', 'preferred_writers',
        'dark_mode', 'language', 'font_size',
        'breaking_news_notifications', 'daily_newsletter', 'weekly_digest',
        'notification_settings',
    ];

    protected $casts = [
        'preferred_categories' => 'array', 'preferred_tags' => 'array',
        'preferred_writers' => 'array', 'notification_settings' => 'array',
        'dark_mode' => 'boolean', 'breaking_news_notifications' => 'boolean',
        'daily_newsletter' => 'boolean', 'weekly_digest' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
