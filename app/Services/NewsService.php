<?php

namespace App\Services;

use App\Models\News;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsService
{
    /**
     * إنشاء خبر جديد
     */
    public function create(array $data, $user): News
    {
        // إنشاء slug
        $slugAr = Str::slug($data['title']['ar']);
        $slugEn = isset($data['title']['en']) ? Str::slug($data['title']['en']) : null;

        // التأكد من تفرد الـ slug
        $originalSlugAr = $slugAr;
        $counter = 1;
        while (News::where('slug->ar', $slugAr)->exists()) {
            $slugAr = $originalSlugAr . '-' . $counter;
            $counter++;
        }

        $news = News::create([
            'title' => $data['title'] ?? [],
            'slug' => ['ar' => $slugAr, 'en' => $slugEn],
            'subtitle' => $data['subtitle'] ?? null,
            'content' => $data['content'] ?? [],
            'excerpt' => $data['excerpt'] ?? null,
            'category_id' => $data['category_id'],
            'sub_category_id' => $data['sub_category_id'] ?? null,
            'writer_id' => $user->role === 'writer' ? $user->id : ($data['writer_id'] ?? null),
            'editor_id' => $user->role === 'editor' ? $user->id : null,
            'user_id' => $user->id,
            'status' => $data['status'] ?? 'draft',
            'priority' => $data['priority'] ?? 'normal',
            'format' => $data['format'] ?? 'standard',
            'location' => $data['location'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'source_name' => $data['source_name'] ?? null,
            'source_url' => $data['source_url'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'no_index' => $data['no_index'] ?? false,
            'allow_comments' => $data['allow_comments'] ?? true,
            'is_sponsored' => $data['is_sponsored'] ?? false,
            'sponsored_by' => $data['sponsored_by'] ?? null,
            'published_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
        ]);

        // حساب وقت القراءة
        $news->updateReadingTime();

        // ربط الوسوم
        if (!empty($data['tags'])) {
            $news->tags()->sync($data['tags']);
        }

        // رفع الصور
        if (request()->hasFile('main_image')) {
            $news->addMediaFromRequest('main_image')
                 ->toMediaCollection('main_images');
        }

        if (request()->hasFile('thumbnail')) {
            $news->addMediaFromRequest('thumbnail')
                 ->toMediaCollection('thumbnails');
        }

        // تحديث عداد التصنيف
        $this->updateCategoryCount($news->category);

        return $news->fresh(['category', 'tags', 'writer']);
    }

    /**
     * تحديث خبر
     */
    public function update(News $news, array $data): News
    {
        // تحديث slug إذا تغير العنوان
        if (isset($data['title'])) {
            $slugAr = Str::slug($data['title']['ar']);
            if ($slugAr !== ($news->getTranslation('slug', 'ar') ?? '')) {
                $originalSlugAr = $slugAr;
                $counter = 1;
                while (News::where('slug->ar', $slugAr)->where('id', '!=', $news->id)->exists()) {
                    $slugAr = $originalSlugAr . '-' . $counter;
                    $counter++;
                }
                $data['slug'] = ['ar' => $slugAr, 'en' => $data['title']['en'] ? Str::slug($data['title']['en']) : null];
            }
        }

        // تحديث تاريخ النشر
        if (isset($data['status']) && $data['status'] === 'published' && !$news->published_at) {
            $data['published_at'] = now();
        }

        $news->update($data);

        // حساب وقت القراءة
        if (isset($data['content'])) {
            $news->updateReadingTime();
        }

        // ربط الوسوم
        if (isset($data['tags'])) {
            $news->tags()->sync($data['tags']);
        }

        // تحديث عداد التصنيف
        if (isset($data['category_id'])) {
            $this->updateCategoryCount($news->category);
            $newCategory = Category::find($data['category_id']);
            if ($newCategory) {
                $this->updateCategoryCount($newCategory);
            }
        }

        return $news->fresh(['category', 'tags', 'writer']);
    }

    /**
     * زيادة المشاهدات
     */
    public function incrementViews(News $news, Request $request): void
    {
        $ip = $request->ip();
        $sessionKey = "viewed_news_{$news->id}";

        if (!session()->has($sessionKey)) {
            $news->increment('views_count');
            session()->put($sessionKey, true);
        }
    }

    /**
     * تحديث عداد التصنيف
     */
    private function updateCategoryCount($category): void
    {
        if ($category) {
            $category->updateNewsCount();

            // تحديث التصنيفات العليا
            $parent = $category->parent;
            while ($parent) {
                $parent->updateNewsCount();
                $parent = $parent->parent;
            }
        }
    }
}
