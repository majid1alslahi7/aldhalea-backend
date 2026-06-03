<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BreakingNews extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content'];

    protected $fillable = ['title', 'content', 'url', 'priority', 'is_active', 'starts_at', 'ends_at', 'user_id'];
    protected $casts = ['title' => 'array', 'content' => 'array', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeActive($query) {
        return $query->where('is_active', true)
                     ->where('starts_at', '<=', now())
                     ->where(function($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); });
    }
}
