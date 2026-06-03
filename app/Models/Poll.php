<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Poll extends Model
{
    use SoftDeletes, HasTranslations;

    public $translatable = ['question', 'description'];

    protected $fillable = [
        'question', 'description', 'status', 'is_featured',
        'allow_multiple', 'show_results',
        'starts_at', 'ends_at', 'user_id',
    ];

    protected $casts = [
        'question' => 'array', 'description' => 'array',
        'is_featured' => 'boolean', 'allow_multiple' => 'boolean',
        'show_results' => 'boolean',
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
    ];

    public function options() { return $this->hasMany(PollOption::class)->orderBy('order'); }
    public function votes() { return $this->hasMany(PollVote::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeActive($query) {
        return $query->where('status', 'active')
                     ->where('starts_at', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                     });
    }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
    public function scopeRecent($query) { return $query->latest(); }

    public function getTotalVotesAttribute() { return $this->votes()->count(); }
    public function getIsActiveAttribute() {
        return $this->status === 'active' && $this->starts_at <= now() && (!$this->ends_at || $this->ends_at >= now());
    }
    public function hasUserVoted($userId) { return $this->votes()->where('user_id', $userId)->exists(); }
}
