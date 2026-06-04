<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentCorrectionReport extends Model
{
    protected $fillable = [
        'reportable_type',
        'reportable_id',
        'content_type',
        'content_title',
        'url',
        'reason',
        'details',
        'evidence_url',
        'reporter_name',
        'reporter_email',
        'status',
        'editor_notes',
        'reviewed_by',
        'reviewed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reportable()
    {
        return $this->morphTo();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
