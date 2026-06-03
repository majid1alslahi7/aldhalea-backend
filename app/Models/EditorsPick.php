<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorsPick extends Model
{
    protected $fillable = ['pickable_type', 'pickable_id', 'order', 'editor_note', 'user_id', 'starts_at', 'ends_at'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function pickable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeActive($query) {
        return $query->where('starts_at', '<=', now())
                     ->where(function($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); });
    }
    public function scopeOrdered($query) { return $query->orderBy('order'); }
}
