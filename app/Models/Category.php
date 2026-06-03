<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['name', 'slug', 'description', 'meta_title', 'meta_description'];

    protected $fillable = [
        'name', 'slug', 'description',
        'parent_id', 'icon', 'color', 'image',
        'order', 'is_active', 'show_in_menu',
        'news_count', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'description' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
    ];

    // العلاقات
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function publishedNews()
    {
        return $this->news()->published();
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true)->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug->ar', $slug)->orWhere('slug->en', $slug);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('news_count', 'desc');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name->ar', 'LIKE', "%{$term}%")
              ->orWhere('name->en', 'LIKE', "%{$term}%")
              ->orWhere('description->ar', 'LIKE', "%{$term}%");
        });
    }

    public function updateNewsCount()
    {
        $this->news_count = $this->news()->published()->count() +
                           $this->children->sum(function($child) {
                               return $child->news()->published()->count();
                           });
        $this->save();
    }

    // إصلاح: حذف getUrlAttribute الذي يستخدم route()
    public function getFullSlugAttribute()
    {
        return $this->getTranslation('slug', app()->getLocale()) ?? $this->getTranslation('slug', 'ar');
    }
}
