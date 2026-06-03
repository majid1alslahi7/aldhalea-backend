<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = ['email', 'name', 'is_active', 'preferences', 'verified_at', 'unsubscribed_at'];

    protected $casts = ['preferences' => 'array', 'is_active' => 'boolean', 'verified_at' => 'datetime', 'unsubscribed_at' => 'datetime'];

    public function scopeActive($query) { return $query->where('is_active', true)->whereNotNull('verified_at')->whereNull('unsubscribed_at'); }
    public function scopeUnverified($query) { return $query->whereNull('verified_at'); }

    public function unsubscribe() { $this->update(['is_active' => false, 'unsubscribed_at' => now()]); }
}
