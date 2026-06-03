<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', $locale),
            'slug' => $this->getTranslation('slug', $locale),
            'description' => $this->getTranslation('description', $locale),
            'color' => $this->color,
            'news_count' => (int) $this->news_count,
            'is_trending' => (bool) $this->is_trending,
        ];
    }
}
