<?php

namespace App\Support;

use Illuminate\Support\Str;

class LocalizedSlug
{
    public static function make(?string $value, ?string $fallback = null): string
    {
        $source = trim((string) ($value ?: $fallback ?: 'item'));
        $slug = Str::slug($source);

        if ($slug !== '') {
            return $slug;
        }

        $slug = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s-]+/u', '', $source) ?: '';
        $slug = preg_replace('/[\s-]+/u', '-', trim($slug)) ?: '';

        return trim($slug, '-') ?: 'item';
    }
}
