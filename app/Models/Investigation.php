<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Investigation extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['title', 'slug', 'subtitle', 'content', 'excerpt', 'meta_title', 'meta_description'];

    protected $fillable = [
        'title', 'slug', 'subtitle', 'content', 'excerpt',
        'main_image', 'thumbnail', 'gallery', 'documents', 'evidence',
        'category_id', 'user_id', 'editor_id',
        'status', 'priority',
        'meta_title', 'meta_description', 'meta_keywords',
        'location', 'people_involved', 'investigation_date',
        'views_count', 'shares_count', 'comments_count', 'reading_time',
        'allow_comments', 'is_confidential', 'published_at',
    ];

    protected $casts = [
        'title' => 'array', 'slug' => 'array', 'content' => 'array',
        'excerpt' => 'array', 'gallery' => 'array',
        'documents' => 'array', 'evidence' => 'array',
        'meta_title' => 'array', 'meta_description' => 'array',
        'people_involved' => 'array',
        'investigation_date' => 'date', 'published_at' => 'datetime',
        'allow_comments' => 'boolean', 'is_confidential' => 'boolean',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function editor() { return $this->belongsTo(User::class, 'editor_id'); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
    public function likes() { return $this->morphMany(Like::class, 'likeable'); }
    public function shares() { return $this->morphMany(Share::class, 'shareable'); }

    public function scopePublished($query) { return $query->where('status', 'published')->where('published_at', '<=', now()); }
    public function scopeFeatured($query) { return $query->where('priority', 'featured'); }
    public function scopeUrgent($query) { return $query->where('priority', 'urgent'); }
    public function scopePopular($query) { return $query->orderBy('views_count', 'desc'); }
    public function scopeRecent($query) { return $query->published()->latest('published_at'); }
    public function scopeSearch($query, $term) {
        return $query->where('title->ar', 'LIKE', "%{$term}%")
                     ->orWhere('content->ar', 'LIKE', "%{$term}%");
    }

    public function getMainImageUrlAttribute() { return $this->main_image ? asset('storage/' . $this->main_image) : null; }
    public function getUrlAttribute() {
        $slug = $this->getTranslation('slug', app()->getLocale()) ?? $this->getTranslation('slug', 'ar');
        return url("/investigations/{$slug}");
    }
}
