<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description', 'is_public'];
    protected $casts = ['is_public' => 'boolean'];

    public function scopeByKey($query, $key) { return $query->where('key', $key); }
    public function scopeByGroup($query, $group) { return $query->where('group', $group); }
    public function scopePublic($query) { return $query->where('is_public', true); }

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
