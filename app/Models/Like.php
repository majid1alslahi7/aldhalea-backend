<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id', 'likeable_type', 'likeable_id', 'type', 'ip_address'];

    public function likeable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeByUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeByType($query, $type) { return $query->where('type', $type); }
    public function scopeLikes($query) { return $query->where('type', 'like'); }
    public function scopeLoves($query) { return $query->where('type', 'love'); }
}
