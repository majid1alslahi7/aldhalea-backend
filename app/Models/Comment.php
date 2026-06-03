<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content', 'commentable_type', 'commentable_id',
        'user_id', 'parent_id',
        'status', 'likes_count', 'replies_count',
        'is_pinned', 'is_edited',
        'ip_address', 'user_agent', 'approved_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean', 'is_edited' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function commentable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }
    public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies() { return $this->hasMany(Comment::class, 'parent_id'); }
    public function likes() { return $this->morphMany(Like::class, 'likeable'); }

    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeParents($query) { return $query->whereNull('parent_id'); }
    public function scopePinned($query) { return $query->where('is_pinned', true); }
    public function scopeRecent($query) { return $query->latest(); }

    public function getIsApprovedAttribute() { return $this->status === 'approved'; }
    public function getTimeAgoAttribute() { return $this->created_at->diffForHumans(); }
    public function getUserNameAttribute() { return $this->user?->name ?? 'مستخدم مجهول'; }
    public function getUserAvatarAttribute() { return $this->user?->avatar_url; }
}
