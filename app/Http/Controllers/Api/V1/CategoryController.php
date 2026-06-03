<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index()
    {
        $categories = Category::active()->ordered()->get();
        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function menu()
    {
        $categories = Category::inMenu()->parents()->ordered()
            ->with(['children' => function($q) {
                $q->inMenu()->ordered();
            }])->get();
            
        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function tree()
    {
        $categories = Category::parents()->ordered()->with('allChildren')->get();
        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function popular()
    {
        $categories = Category::active()->popular()->limit(10)->get();
        return $this->successResponse(CategoryResource::collection($categories));
    }

    public function show($slug)
    {
        $category = Category::bySlug($slug)->with('children')->first();
        if (!$category) {
            return $this->notFoundResponse('التصنيف غير موجود');
        }

        return $this->successResponse(new CategoryResource($category));
    }

    public function news($slug, Request $request)
    {
        $category = Category::bySlug($slug)->first();
        if (!$category) {
            return $this->notFoundResponse('التصنيف غير موجود');
        }

        $news = $category->publishedNews()
                         ->with(['category', 'writer', 'tags'])
                         ->latest('published_at')
                         ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($news);
    }

    public function subcategories($slug)
    {
        $category = Category::bySlug($slug)->first();
        if (!$category) {
            return $this->notFoundResponse('التصنيف غير موجود');
        }

        return $this->successResponse(CategoryResource::collection($category->children));
    }

    // Admin methods
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'description.ar' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
        ]);

        $category = Category::create($validated);
        return $this->createdResponse(new CategoryResource($category), 'تم إنشاء التصنيف');
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return $this->notFoundResponse();

        $category->update($request->all());
        return $this->updatedResponse(new CategoryResource($category));
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return $this->notFoundResponse();

        $category->delete();
        return $this->deletedResponse();
    }
}
