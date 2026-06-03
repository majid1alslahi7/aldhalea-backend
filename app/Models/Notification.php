<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message',
        'notifiable_type', 'notifiable_id', 'data', 'is_read', 'read_at',
    ];

    protected $casts = ['data' => 'array', 'is_read' => 'boolean', 'read_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function notifiable() { return $this->morphTo(); }

    public function scopeUnread($query) { return $query->where('is_read', false); }
    public function scopeRead($query) { return $query->where('is_read', true); }
    public function scopeByType($query, $type) { return $query->where('type', $type); }
    public function scopeRecent($query) { return $query->latest(); }

    public function markAsRead() { $this->update(['is_read' => true, 'read_at' => now()]); }
}
