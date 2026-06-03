<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'title_ar' => $this->getTranslation('title', 'ar'),
            'title_en' => $this->getTranslation('title', 'en'),
            'slug' => $this->getTranslation('slug', $locale),
            'subtitle' => $this->getTranslation('subtitle', $locale),
            'content' => $this->getTranslation('content', $locale),
            'excerpt' => $this->getTranslation('excerpt', $locale),

            // الوسائط
            'main_image' => $this->main_image_url,
            'thumbnail' => $this->thumbnail_url,
            'featured_video' => $this->featured_video,
            'gallery' => $this->when($this->gallery, $this->gallery),

            // العلاقات
            'category' => new CategoryResource($this->whenLoaded('category')),
            'sub_category' => new CategoryResource($this->whenLoaded('subCategory')),
            'writer' => new WriterResource($this->whenLoaded('writer')),
            'editor' => new WriterResource($this->whenLoaded('editor')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            // التعليقات
            'comments' => CommentResource::collection($this->whenLoaded('approvedComments')),
            'comments_count' => $this->when($this->comments_count, $this->comments_count),

            // الحالة والمعلومات
            'status' => $this->status,
            'priority' => $this->priority,
            'format' => $this->format,
            'is_breaking' => $this->is_breaking,
            'breaking_until' => $this->breaking_until?->toISOString(),

            // الموقع
            'location' => $this->location,
            'city' => $this->city,
            'district' => $this->district,
            'coordinates' => $this->when($this->latitude && $this->longitude, [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ]),

            // المصدر
            'source' => $this->when($this->source_name, [
                'name' => $this->source_name,
                'url' => $this->source_url,
            ]),

            // الإحصائيات
            'stats' => [
                'views' => (int) $this->views_count,
                'unique_views' => (int) $this->unique_views,
                'shares' => (int) $this->shares_count,
                'comments' => (int) $this->comments_count,
                'likes' => (int) $this->likes_count,
                'bookmarks' => (int) $this->bookmarks_count,
            ],
            'reading_time' => $this->when($this->reading_time, [
                'minutes' => $this->reading_time,
                'formatted' => $this->reading_time_formatted,
            ]),

            // SEO
            'seo' => [
                'meta_title' => $this->getTranslation('meta_title', $locale),
                'meta_description' => $this->getTranslation('meta_description', $locale),
                'meta_keywords' => $this->meta_keywords,
                'canonical_url' => $this->canonical_url,
                'no_index' => (bool) $this->no_index,
            ],

            // إعدادات
            'allow_comments' => (bool) $this->allow_comments,
            'is_sponsored' => (bool) $this->is_sponsored,
            'sponsored_by' => $this->when($this->is_sponsored, $this->sponsored_by),

            // التواريخ
            'published_at' => $this->published_at?->toISOString(),
            'published_date' => $this->published_at?->format('Y-m-d'),
            'published_time' => $this->published_at?->format('H:i'),
            'published_diff' => $this->published_date_formatted,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // الروابط
            'url' => $this->url,
        ];
    }
}
