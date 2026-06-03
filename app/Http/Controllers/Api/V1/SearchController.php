<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\News;
use App\Models\Article;
use App\Models\SearchLog;
use App\Http\Resources\NewsResource;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:120',
            'type' => 'nullable|in:all,news,articles',
            'per_page' => 'nullable|integer|min:1|max:30',
        ]);

        $query = trim($validated['q']);
        $type = $validated['type'] ?? 'all';
        $perPage = $validated['per_page'] ?? 15;

        $results = collect();

        if ($type === 'all' || $type === 'news') {
            $newsResults = News::published()->search($query)->with(['category', 'writer'])->latest()->take($perPage)->get();
            $results = $results->merge($newsResults->map(fn($item) => [
                'type' => 'news',
                'data' => new NewsResource($item),
            ]));
        }

        if ($type === 'all' || $type === 'articles') {
            $articleResults = Article::published()->search($query)->with(['writer', 'category'])->latest()->take($perPage)->get();
            $results = $results->merge($articleResults->map(fn($item) => [
                'type' => 'article',
                'data' => new ArticleResource($item),
            ]));
        }

        $this->logSearch($query, $request, $results->count());

        return $this->successResponse([
            'query' => $query,
            'total' => $results->count(),
            'results' => $results,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = trim((string) $request->get('q'));
        if (mb_strlen($query) < 2 || mb_strlen($query) > 120) {
            return $this->successResponse([]);
        }

        $newsTitles = News::published()
                          ->where('title->ar', 'LIKE', "%{$query}%")
                          ->limit(5)
                          ->pluck('title->ar');

        return $this->successResponse($newsTitles);
    }

    public function trendingSearches()
    {
        $trending = SearchLog::popular()->limit(10)->pluck('query');
        return $this->successResponse($trending);
    }

    private function logSearch($query, $request, int $resultsCount): void
    {
        SearchLog::create([
            'query' => $query,
            'user_id' => $request->user()?->id,
            'results_count' => $resultsCount,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'filters' => $request->except(['q', 'page']),
        ]);
    }
}
