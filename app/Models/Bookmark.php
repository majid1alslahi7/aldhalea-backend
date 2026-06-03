<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = ['user_id', 'bookmarkable_type', 'bookmarkable_id', 'note'];

    public function bookmarkable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeByUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeByType($query, $type) { return $query->where('bookmarkable_type', $type); }
    public function scopeRecent($query) { return $query->latest(); }
}
