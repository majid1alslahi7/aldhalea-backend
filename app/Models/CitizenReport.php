<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CitizenReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'location', 'latitude', 'longitude', 'media',
        'reporter_name', 'reporter_email', 'reporter_phone',
        'user_id', 'status', 'editor_notes', 'reviewed_by', 'reviewed_at', 'ip_address',
    ];

    protected $casts = ['media' => 'array', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'reviewed_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePublished($query) { return $query->where('status', 'published'); }
    public function scopeRecent($query) { return $query->latest(); }
}
