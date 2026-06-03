<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'slug' => $this->getTranslation('slug', $locale),
            'content' => $this->getTranslation('content', $locale),
            'excerpt' => $this->getTranslation('excerpt', $locale),
            'featured_image' => $this->featured_image_url,
            'type' => $this->type,
            'priority' => $this->priority,
            'writer' => new WriterResource($this->whenLoaded('writer')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'stats' => [
                'views' => (int) $this->views_count,
                'shares' => (int) $this->shares_count,
                'comments' => (int) $this->comments_count,
                'likes' => (int) $this->likes_count,
            ],
            'reading_time' => $this->reading_time,
            'url' => $this->url,
            'published_at' => $this->published_at?->toISOString(),
            'published_diff' => $this->published_at?->diffForHumans(),
        ];
    }
}
