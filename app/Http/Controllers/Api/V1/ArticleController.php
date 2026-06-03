<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends BaseController
{
    public function index(Request $request)
    {
        $query = Article::published()->with(['writer', 'category', 'tags']);

        if ($request->has('type')) $query->byType($request->type);
        if ($request->has('category')) $query->whereHas('category', fn($q) => $q->where('slug->ar', $request->category));
        if ($request->has('tag')) $query->whereHas('tags', fn($q) => $q->where('slug->ar', $request->tag));
        if ($request->has('search')) $query->search($request->search);

        $articles = $query->latest('published_at')->paginate($request->get('per_page', 12));

        return $this->paginatedResponse($articles);
    }

    public function opinions(Request $request)
    {
        return $this->index($request->merge(['type' => 'opinion']));
    }

    public function analysis(Request $request)
    {
        return $this->index($request->merge(['type' => 'analysis']));
    }

    public function columns(Request $request)
    {
        return $this->index($request->merge(['type' => 'column']));
    }

    public function featured()
    {
        $featured = Cache::remember('featured_articles', 600, function () {
            return Article::published()->featured()->with(['writer', 'category'])->latest()->limit(5)->get();
        });

        return $this->successResponse(ArticleResource::collection($featured));
    }

    public function popular()
    {
        $popular = Cache::remember('popular_articles', 1800, function () {
            return Article::published()->popular()->with('writer')->limit(10)->get();
        });

        return $this->successResponse(ArticleResource::collection($popular));
    }

    public function latest()
    {
        $latest = Article::recent()->with(['writer', 'category'])->limit(15)->get();
        return $this->successResponse(ArticleResource::collection($latest));
    }

    public function byWriter($writerId)
    {
        $articles = Article::published()->byWriter($writerId)->with('category')->latest()->paginate(12);
        return $this->paginatedResponse($articles);
    }

    public function show($slug)
    {
        $article = Article::where('slug->ar', $slug)->orWhere('slug->en', $slug)
                          ->with(['writer', 'category', 'tags', 'approvedComments'])
                          ->first();

        if (!$article) return $this->notFoundResponse();

        $article->increment('views_count');

        return $this->successResponse(new ArticleResource($article));
    }

    public function related($id)
    {
        $article = Article::find($id);
        if (!$article) return $this->notFoundResponse();

        $related = Article::published()->where('id', '!=', $id)
                          ->where(function($q) use ($article) {
                              $q->where('category_id', $article->category_id)
                                ->orWhere('writer_id', $article->writer_id);
                          })->limit(4)->get();

        return $this->successResponse(ArticleResource::collection($related));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.ar' => 'required|string|max:255',
            'content.ar' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,pending,published',
            'type' => 'nullable|in:opinion,analysis,blog,column,feature',
            'tags' => 'nullable|array',
            'featured_image' => 'nullable|image|max:5120',
        ]);

        $article = Article::create(array_merge($validated, [
            'writer_id' => $request->user()->id,
            'slug' => ['ar' => \Str::slug($validated['title']['ar'])],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]));

        if (!empty($validated['tags'])) $article->tags()->sync($validated['tags']);
        if ($request->hasFile('featured_image')) $article->addMediaFromRequest('featured_image')->toMediaCollection('featured');

        return $this->createdResponse(new ArticleResource($article));
    }

    public function update(Request $request, $id)
    {
        $article = Article::find($id);
        if (!$article) return $this->notFoundResponse();

        $article->update($request->all());
        if ($request->has('tags')) $article->tags()->sync($request->tags);

        return $this->updatedResponse(new ArticleResource($article));
    }

    public function destroy($id)
    {
        $article = Article::find($id);
        if (!$article) return $this->notFoundResponse();

        $article->delete();
        return $this->deletedResponse();
    }
}
