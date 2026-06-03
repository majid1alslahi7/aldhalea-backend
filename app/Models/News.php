<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class News extends Model implements HasMedia
{
    use SoftDeletes, HasTranslations, InteractsWithMedia;

    public $translatable = [
        'title', 'slug', 'subtitle', 'content', 'excerpt',
        'meta_title', 'meta_description'
    ];

    protected $fillable = [
        'title', 'slug', 'subtitle', 'content', 'excerpt',
        'main_image', 'thumbnail', 'featured_video', 'gallery',
        'category_id', 'sub_category_id', 'writer_id', 'editor_id', 'user_id',
        'status', 'priority', 'format',
        'meta_title', 'meta_description', 'meta_keywords',
        'canonical_url', 'no_index',
        'location', 'city', 'district', 'latitude', 'longitude',
        'source_name', 'source_url',
        'views_count', 'unique_views', 'shares_count',
        'comments_count', 'likes_count', 'bookmarks_count',
        'reading_time',
        'allow_comments', 'is_sponsored', 'sponsored_by',
        'published_at', 'breaking_until',
    ];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'excerpt' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'gallery' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'published_at' => 'datetime',
        'breaking_until' => 'datetime',
        'allow_comments' => 'boolean',
        'is_sponsored' => 'boolean',
        'no_index' => 'boolean',
    ];

    // ============ العلاقات ============
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'news_tag')->withTimestamps();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments()
    {
        return $this->comments()->approved()->parents();
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

    public function scopeBreaking($query)
    {
        return $query->where(function ($q) {
                         $q->where('status', 'breaking')
                           ->orWhere('priority', 'breaking');
                     })
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('breaking_until')
                           ->orWhere('breaking_until', '>=', now());
                     });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeLocal($query)
    {
        return $query->whereNotNull('location');
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByDistrict($query, $district)
    {
        return $query->where('district', $district);
    }

    public function scopeFeatured($query)
    {
        return $query->where('priority', 'featured');
    }

    public function scopeEditorsPick($query)
    {
        return $query->where('priority', 'editors_pick');
    }

    public function scopeTrending($query)
    {
        return $query->where('priority', 'trending')
                     ->orderBy('views_count', 'desc');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where(function($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
              ->orWhere('sub_category_id', $categoryId);
        });
    }

    public function scopeByTag($query, $tagId)
    {
        return $query->whereHas('tags', function($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    public function scopeByWriter($query, $writerId)
    {
        return $query->where('writer_id', $writerId);
    }

    public function scopePopular($query, $days = 7)
    {
        return $query->published()
                     ->where('published_at', '>=', now()->subDays($days))
                     ->orderBy('views_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->published()->latest('published_at');
    }

    public function scopeRelated($query, $news)
    {
        return $query->published()
                     ->where('id', '!=', $news->id)
                     ->where(function($q) use ($news) {
                         $q->where('category_id', $news->category_id)
                           ->orWhere('sub_category_id', $news->category_id)
                           ->orWhereHas('tags', function($q2) use ($news) {
                               $q2->whereIn('tags.id', $news->tags->pluck('id'));
                           });
                     });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title->ar', 'LIKE', "%{$term}%")
              ->orWhere('title->en', 'LIKE', "%{$term}%")
              ->orWhere('content->ar', 'LIKE', "%{$term}%")
              ->orWhere('content->en', 'LIKE', "%{$term}%")
              ->orWhere('excerpt->ar', 'LIKE', "%{$term}%")
              ->orWhere('meta_keywords', 'LIKE', "%{$term}%")
              ->orWhere('location', 'LIKE', "%{$term}%");
        });
    }

    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeDateBetween($query, $start, $end)
    {
        return $query->whereBetween('published_at', [$start, $end]);
    }

    public function scopeTodayNews($query)
    {
        return $query->whereDate('published_at', today());
    }

    // ============ Media Library ============
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_images')
             ->singleFile()
             ->useDisk('public');

        $this->addMediaCollection('gallery')
             ->useDisk('public');

        $this->addMediaCollection('thumbnails')
             ->singleFile()
             ->useDisk('public');
    }

    // ============ Methods ============
    public function incrementViews()
    {
        $this->increment('views_count');
        $this->increment('unique_views');
    }

    public function toggleBookmark($userId)
    {
        $bookmark = $this->bookmarks()->where('user_id', $userId)->first();
        if ($bookmark) {
            $bookmark->delete();
            $this->decrement('bookmarks_count');
            return false;
        } else {
            $this->bookmarks()->create(['user_id' => $userId]);
            $this->increment('bookmarks_count');
            return true;
        }
    }

    public function updateReadingTime()
    {
        $wordCount = str_word_count(strip_tags($this->getTranslation('content', 'ar') ?? ''));
        $this->reading_time = max(1, ceil($wordCount / 200));
        $this->save();
    }

    public function getRelatedNews($limit = 4)
    {
        return self::related($this)->limit($limit)->get();
    }

    public function getNextNews()
    {
        return self::published()
                   ->where('category_id', $this->category_id)
                   ->where('id', '>', $this->id)
                   ->oldest('id')
                   ->first();
    }

    public function getPreviousNews()
    {
        return self::published()
                   ->where('category_id', $this->category_id)
                   ->where('id', '<', $this->id)
                   ->latest('id')
                   ->first();
    }

    // ============ Attributes ============
    public function getMainImageUrlAttribute()
    {
        if ($this->main_image && filter_var($this->main_image, FILTER_VALIDATE_URL)) {
            return $this->main_image;
        }

        return $this->main_image ? asset('storage/' . $this->main_image) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail && filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }

        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : $this->main_image_url;
    }

    public function getUrlAttribute()
    {
        $slug = $this->getTranslation('slug', app()->getLocale()) ?? $this->getTranslation('slug', 'ar');
        return url("/news/{$slug}");
    }

    public function getIsBreakingAttribute()
    {
        return $this->status === 'breaking' &&
               (!$this->breaking_until || $this->breaking_until >= now());
    }

    public function getReadingTimeFormattedAttribute()
    {
        return $this->reading_time . ' ' . __('دقيقة');
    }

    public function getPublishedDateFormattedAttribute()
    {
        return $this->published_at?->diffForHumans();
    }
}
