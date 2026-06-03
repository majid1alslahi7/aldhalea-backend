<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\News;
use App\Models\Article;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'my_news' => News::where('user_id', $user->id)->count(),
            'published_news' => News::where('user_id', $user->id)->published()->count(),
            'draft_news' => News::where('user_id', $user->id)->draft()->count(),
            'my_articles' => Article::where('writer_id', $user->id)->count(),
            'total_views' => News::where('user_id', $user->id)->sum('views_count'),
            'total_comments' => News::where('user_id', $user->id)->sum('comments_count'),
        ];

        return $this->successResponse($stats);
    }

    public function analytics(Request $request)
    {
        $days = $request->get('days', 30);

        $analytics = [
            'views_by_day' => News::where('user_id', $request->user()->id)
                                  ->where('created_at', '>=', now()->subDays($days))
                                  ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(views_count) as views'))
                                  ->groupBy('date')
                                  ->orderBy('date')
                                  ->get(),
            'top_news' => News::where('user_id', $request->user()->id)
                              ->orderBy('views_count', 'desc')
                              ->limit(5)
                              ->get(['id', 'title', 'views_count']),
            'by_category' => News::where('user_id', $request->user()->id)
                                 ->with('category')
                                 ->select('category_id', DB::raw('COUNT(*) as count'))
                                 ->groupBy('category_id')
                                 ->get(),
        ];

        return $this->successResponse($analytics);
    }

    public function recentActivity(Request $request)
    {
        $activity = collect();

        // آخر التعليقات
        $comments = Comment::whereHasMorph('commentable', [News::class], function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->latest()->limit(5)->get();

        $activity = $activity->merge($comments->map(fn($c) => [
            'type' => 'comment',
            'data' => $c,
            'date' => $c->created_at,
        ]));

        return $this->successResponse($activity->sortByDesc('date')->values());
    }

    public function adminOverview()
    {
        return $this->successResponse([
            'total_news' => News::count(),
            'published_news' => News::published()->count(),
            'total_articles' => Article::count(),
            'total_users' => User::count(),
            'total_comments' => Comment::count(),
            'pending_comments' => Comment::pending()->count(),
            'today_views' => News::todayNews()->sum('views_count'),
        ]);
    }

    public function trafficStats() { return $this->successResponse([]); }
    public function contentStats() { return $this->successResponse([]); }
    public function userStats() { return $this->successResponse([]); }
    public function exportStats() { return $this->successResponse([]); }
}
