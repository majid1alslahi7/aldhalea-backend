<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends BaseController
{
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'likeable_type' => 'required|string',
            'likeable_id' => 'required|integer',
            'type' => 'nullable|in:like,love,wow,sad,angry',
        ]);

        $modelClass = $this->getModelClass($validated['likeable_type']);
        if (!$modelClass || !$model = $modelClass::find($validated['likeable_id'])) {
            return $this->notFoundResponse();
        }

        $like = Like::where([
            'user_id' => $request->user()->id,
            'likeable_type' => $modelClass,
            'likeable_id' => $validated['likeable_id'],
        ])->first();

        if ($like) {
            $like->delete();
            $model->decrement('likes_count');
            return $this->successResponse(['liked' => false, 'count' => $model->likes_count]);
        }

        Like::create([
            'user_id' => $request->user()->id,
            'likeable_type' => $modelClass,
            'likeable_id' => $validated['likeable_id'],
            'type' => $validated['type'] ?? 'like',
            'ip_address' => $request->ip(),
        ]);
        $model->increment('likes_count');

        return $this->successResponse(['liked' => true, 'count' => $model->likes_count]);
    }

    public function myLikes(Request $request)
    {
        $likes = Like::byUser($request->user()->id)->latest()->paginate(20);
        return $this->paginatedResponse($likes);
    }

    private function getModelClass($type)
    {
        return match($type) {
            'news' => \App\Models\News::class,
            'article' => \App\Models\Article::class,
            'comment' => \App\Models\Comment::class,
            'report' => \App\Models\Report::class,
            default => null,
        };
    }
}
