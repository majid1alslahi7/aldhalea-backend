<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaGallery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'file_path', 'file_type', 'mime_type',
        'file_size', 'dimensions', 'meta', 'alt_text', 'caption', 'credit', 'user_id',
    ];

    protected $casts = ['dimensions' => 'array', 'meta' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeImages($query) { return $query->where('file_type', 'image'); }
    public function scopeVideos($query) { return $query->where('file_type', 'video'); }
    public function scopeDocuments($query) { return $query->where('file_type', 'document'); }
    public function scopeRecent($query) { return $query->latest(); }

    public function getFileUrlAttribute() { return asset('storage/' . $this->file_path); }
}
