<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $fillable = ['query', 'user_id', 'results_count', 'ip_address', 'user_agent', 'filters'];
    protected $casts = ['filters' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    public function scopePopular($query) { return $query->orderByRaw('COUNT(*) DESC')->groupBy('query'); }
    public function scopeRecent($query) { return $query->latest(); }
}
