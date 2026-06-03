<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    protected $fillable = ['user_id', 'shareable_type', 'shareable_id', 'platform', 'ip_address'];

    public function shareable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeByPlatform($query, $platform) { return $query->where('platform', $platform); }
    public function scopeFacebook($query) { return $query->where('platform', 'facebook'); }
    public function scopeTwitter($query) { return $query->where('platform', 'twitter'); }
    public function scopeWhatsapp($query) { return $query->where('platform', 'whatsapp'); }
}
