<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\News;
use App\Models\Article;
use App\Models\Report;
use App\Models\SearchLog;
use App\Http\Resources\NewsResource;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    public function index(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // all, news, articles, reports
        $perPage = $request->get('per_page', 15);

        if (!$query) {
            return $this->errorResponse('يرجى إدخال كلمة البحث');
        }

        // تسجيل البحث
        $this->logSearch($query, $request);

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

        return $this->successResponse([
            'query' => $query,
            'total' => $results->count(),
            'results' => $results,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = $request->get('q');
        if (!$query || strlen($query) < 2) {
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

    private function logSearch($query, $request)
    {
        SearchLog::create([
            'query' => $query,
            'user_id' => $request->user()?->id,
            'results_count' => 0,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'filters' => $request->except(['q', 'page']),
        ]);
    }
}
