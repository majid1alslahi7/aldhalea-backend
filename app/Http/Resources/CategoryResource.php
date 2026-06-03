<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', $locale),
            'name_ar' => $this->getTranslation('name', 'ar'),
            'name_en' => $this->getTranslation('name', 'en'),
            'slug' => $this->full_slug,
            'description' => $this->getTranslation('description', $locale),
            'icon' => $this->icon,
            'color' => $this->color,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'news_count' => (int) $this->news_count,
            'is_active' => (bool) $this->is_active,
            'show_in_menu' => (bool) $this->show_in_menu,
            'url' => $this->url,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'seo' => [
                'meta_title' => $this->getTranslation('meta_title', $locale),
                'meta_description' => $this->getTranslation('meta_description', $locale),
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
