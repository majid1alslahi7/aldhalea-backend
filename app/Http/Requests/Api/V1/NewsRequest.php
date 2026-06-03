<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['writer', 'editor', 'admin']);
    }

    public function rules(): array
    {
        $rules = [
            'title.ar' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'subtitle.ar' => 'nullable|string|max:255',
            'subtitle.en' => 'nullable|string|max:255',
            'content.ar' => 'required|string',
            'content.en' => 'nullable|string',
            'excerpt.ar' => 'nullable|string|max:500',
            'excerpt.en' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,pending,published',
            'priority' => 'nullable|in:normal,featured,breaking,editors_pick,trending',
            'format' => 'nullable|in:standard,video,gallery,audio,live',
            'location' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'source_name' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:500',
            'meta_title.ar' => 'nullable|string|max:100',
            'meta_title.en' => 'nullable|string|max:100',
            'meta_description.ar' => 'nullable|string|max:200',
            'meta_description.en' => 'nullable|string|max:200',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'no_index' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'is_sponsored' => 'nullable|boolean',
            'sponsored_by' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'featured_video' => 'nullable|string|max:500',
        ];

        // قواعد إضافية للتحديث
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title.ar'] = 'sometimes|string|max:255';
            $rules['content.ar'] = 'sometimes|string';
            $rules['category_id'] = 'sometimes|exists:categories,id';
            $rules['status'] = 'sometimes|in:draft,pending,published,archived,breaking,rejected';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.ar.required' => 'العنوان بالعربية مطلوب',
            'title.ar.max' => 'العنوان يجب ألا يتجاوز 255 حرف',
            'content.ar.required' => 'المحتوى بالعربية مطلوب',
            'category_id.required' => 'التصنيف مطلوب',
            'category_id.exists' => 'التصنيف غير موجود',
            'status.required' => 'الحالة مطلوبة',
            'status.in' => 'الحالة غير صالحة',
            'tags.*.exists' => 'أحد الوسوم غير موجود',
            'main_image.image' => 'الملف يجب أن يكون صورة',
            'main_image.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
        ];
    }
}
