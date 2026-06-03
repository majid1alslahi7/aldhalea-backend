<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Comment;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;

class CommentController extends BaseController
{
    public function index($type, $id)
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass || !$model = $modelClass::find($id)) {
            return $this->notFoundResponse();
        }

        $comments = $model->comments()->approved()->parents()->with(['user', 'replies.user'])->latest()->paginate(20);

        return $this->paginatedResponse($comments);
    }

    public function pinned($type, $id)
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass || !$model = $modelClass::find($id)) {
            return $this->notFoundResponse();
        }

        $pinned = $model->comments()->approved()->pinned()->with('user')->get();
        return $this->successResponse(CommentResource::collection($pinned));
    }

    public function store($type, $id, Request $request)
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass || !$model = $modelClass::find($id)) {
            return $this->notFoundResponse();
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $model->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => 'approved', // أو pending حسب الإعدادات
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'approved_at' => now(),
        ]);

        $model->increment('comments_count');

        return $this->createdResponse(new CommentResource($comment->load('user')));
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->notFoundResponse();
        if ($comment->user_id !== $request->user()->id) return $this->unauthorizedResponse();

        $comment->update(['content' => $request->content, 'is_edited' => true]);
        return $this->updatedResponse(new CommentResource($comment));
    }

    public function destroy($id, Request $request)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->notFoundResponse();
        if ($comment->user_id !== $request->user()->id && !in_array($request->user()->role, ['editor', 'admin'])) {
            return $this->unauthorizedResponse();
        }

        $comment->delete();
        return $this->deletedResponse();
    }

    public function toggleLike($id, Request $request)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->notFoundResponse();

        $like = $comment->likes()->where('user_id', $request->user()->id)->first();
        if ($like) {
            $like->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            $comment->likes()->create(['user_id' => $request->user()->id]);
            $comment->increment('likes_count');
            $liked = true;
        }

        return $this->successResponse(['liked' => $liked, 'count' => $comment->likes_count]);
    }

    // Admin methods
    public function pending()
    {
        $comments = Comment::pending()->with(['user', 'commentable'])->latest()->paginate(20);
        return $this->paginatedResponse($comments);
    }

    public function approve($id)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->notFoundResponse();

        $comment->update(['status' => 'approved', 'approved_at' => now()]);
        return $this->successResponse(null, 'تمت الموافقة');
    }

    public function reject($id)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->notFoundResponse();

        $comment->update(['status' => 'rejected']);
        return $this->successResponse(null, 'تم الرفض');
    }

    public function togglePin($id)
    {
        $comment = Comment::find($id);
        if (!$comment) return $this->notFoundResponse();

        $comment->update(['is_pinned' => !$comment->is_pinned]);
        return $this->successResponse(['pinned' => $comment->is_pinned]);
    }

    private function getModelClass($type)
    {
        return match($type) {
            'news' => \App\Models\News::class,
            'articles' => \App\Models\Article::class,
            'reports' => \App\Models\Report::class,
            'investigations' => \App\Models\Investigation::class,
            'interviews' => \App\Models\Interview::class,
            default => null,
        };
    }
}
