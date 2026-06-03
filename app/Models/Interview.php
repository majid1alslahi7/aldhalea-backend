<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Interview extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['title', 'slug', 'content', 'excerpt', 'meta_title', 'meta_description'];

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'main_image', 'thumbnail', 'featured_video',
        'interviewee_name', 'interviewee_title', 'interviewee_photo', 'interviewee_bio', 'interviewee_social',
        'category_id', 'interviewer_id', 'editor_id',
        'status', 'type', 'priority',
        'location', 'interview_date', 'duration',
        'meta_title', 'meta_description',
        'views_count', 'shares_count', 'comments_count',
        'allow_comments', 'published_at',
    ];

    protected $casts = [
        'title' => 'array', 'slug' => 'array', 'content' => 'array',
        'excerpt' => 'array', 'meta_title' => 'array', 'meta_description' => 'array',
        'interviewee_social' => 'array',
        'interview_date' => 'date', 'published_at' => 'datetime',
        'allow_comments' => 'boolean',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function interviewer() { return $this->belongsTo(User::class, 'interviewer_id'); }
    public function editor() { return $this->belongsTo(User::class, 'editor_id'); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
    public function likes() { return $this->morphMany(Like::class, 'likeable'); }
    public function shares() { return $this->morphMany(Share::class, 'shareable'); }

    public function scopePublished($query) { return $query->where('status', 'published')->where('published_at', '<=', now()); }
    public function scopeByType($query, $type) { return $query->where('type', $type); }
    public function scopeVideoInterviews($query) { return $query->where('type', 'video'); }
    public function scopePodcastInterviews($query) { return $query->where('type', 'podcast'); }
    public function scopeFeatured($query) { return $query->where('priority', 'featured'); }
    public function scopePopular($query) { return $query->orderBy('views_count', 'desc'); }
    public function scopeRecent($query) { return $query->published()->latest('published_at'); }
    public function scopeSearch($query, $term) {
        return $query->where('title->ar', 'LIKE', "%{$term}%")
                     ->orWhere('interviewee_name', 'LIKE', "%{$term}%");
    }

    public function getMainImageUrlAttribute() { return $this->main_image ? asset('storage/' . $this->main_image) : null; }
    public function getUrlAttribute() {
        $slug = $this->getTranslation('slug', app()->getLocale()) ?? $this->getTranslation('slug', 'ar');
        return url("/interviews/{$slug}");
    }
}
