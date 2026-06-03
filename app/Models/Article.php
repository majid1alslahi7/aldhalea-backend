<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['title', 'slug', 'subtitle', 'content', 'excerpt', 'meta_title', 'meta_description'];

    protected $fillable = [
        'title', 'slug', 'subtitle', 'content', 'excerpt',
        'featured_image', 'thumbnail',
        'category_id', 'writer_id', 'editor_id',
        'status', 'type', 'priority',
        'meta_title', 'meta_description', 'meta_keywords',
        'views_count', 'shares_count', 'comments_count',
        'likes_count', 'bookmarks_count', 'reading_time',
        'allow_comments', 'is_sponsored',
        'published_at',
    ];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'excerpt' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'published_at' => 'datetime',
        'allow_comments' => 'boolean',
        'is_sponsored' => 'boolean',
    ];

    // ============ العلاقات ============
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag')->withTimestamps();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function shares()
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    // ============ Scopes ============
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOpinions($query)
    {
        return $query->where('type', 'opinion');
    }

    public function scopeAnalysis($query)
    {
        return $query->where('type', 'analysis');
    }

    public function scopeColumns($query)
    {
        return $query->where('type', 'column');
    }

    public function scopeByWriter($query, $writerId)
    {
        return $query->where('writer_id', $writerId);
    }

    public function scopeFeatured($query)
    {
        return $query->where('priority', 'featured');
    }

    public function scopeEditorsPick($query)
    {
        return $query->where('priority', 'editors_pick');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('views_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->published()->latest('published_at');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title->ar', 'LIKE', "%{$term}%")
              ->orWhere('title->en', 'LIKE', "%{$term}%")
              ->orWhere('content->ar', 'LIKE', "%{$term}%")
              ->orWhere('excerpt->ar', 'LIKE', "%{$term}%");
        });
    }

    // ============ Attributes ============
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image && filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
            return $this->featured_image;
        }

        return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
    }

    public function getUrlAttribute()
    {
        $slug = $this->getTranslation('slug', app()->getLocale()) ?? $this->getTranslation('slug', 'ar');
        return url("/articles/{$slug}");
    }

    public function getWriterNameAttribute()
    {
        return $this->writer?->name;
    }

    public function getWriterAvatarAttribute()
    {
        return $this->writer?->avatar_url;
    }
}
