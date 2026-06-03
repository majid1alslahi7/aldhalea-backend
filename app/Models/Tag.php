<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['name', 'slug', 'description'];

    protected $fillable = ['name', 'slug', 'description', 'color', 'news_count', 'is_active', 'is_trending'];

    protected $casts = [
        'name' => 'array', 'slug' => 'array', 'description' => 'array',
        'is_active' => 'boolean', 'is_trending' => 'boolean',
    ];

    public function news() { return $this->belongsToMany(News::class, 'news_tag')->withTimestamps(); }
    public function articles() { return $this->belongsToMany(Article::class, 'article_tag')->withTimestamps(); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeTrending($query) { return $query->where('is_trending', true); }
    public function scopeWithContent($query) { return $query->where('news_count', '>', 0); }
    public function scopeSearch($query, $term) {
        return $query->where('name->ar', 'LIKE', "%{$term}%")
                     ->orWhere('name->en', 'LIKE', "%{$term}%");
    }

    public function updateNewsCount() { $this->news_count = $this->news()->published()->count(); $this->save(); }
}
