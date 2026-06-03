<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'type', 'image', 'code', 'url', 'placement', 'order',
        'is_active', 'starts_at', 'ends_at',
        'views_count', 'clicks_count', 'targeting', 'user_id',
    ];

    protected $casts = [
        'targeting' => 'array', 'is_active' => 'boolean',
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeActive($query) {
        return $query->where('is_active', true)
                     ->where('starts_at', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                     });
    }
    public function scopeByPlacement($query, $placement) { return $query->where('placement', $placement); }
    public function scopeByType($query, $type) { return $query->where('type', $type); }
    public function scopeOrdered($query) { return $query->orderBy('order'); }

    public function incrementViews() { $this->increment('views_count'); }
    public function incrementClicks() { $this->increment('clicks_count'); }
}
