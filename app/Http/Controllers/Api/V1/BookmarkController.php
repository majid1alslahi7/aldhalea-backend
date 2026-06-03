<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends BaseController
{
    public function index(Request $request)
    {
        $bookmarks = Bookmark::byUser($request->user()->id)
                             ->with('bookmarkable')
                             ->recent()
                             ->paginate(20);

        return $this->paginatedResponse($bookmarks);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'bookmarkable_type' => 'required|string',
            'bookmarkable_id' => 'required|integer',
        ]);

        $modelClass = match($validated['bookmarkable_type']) {
            'news' => \App\Models\News::class,
            'article' => \App\Models\Article::class,
            default => null,
        };

        if (!$modelClass || !$model = $modelClass::find($validated['bookmarkable_id'])) {
            return $this->notFoundResponse();
        }

        $bookmark = Bookmark::where([
            'user_id' => $request->user()->id,
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id' => $validated['bookmarkable_id'],
        ])->first();

        if ($bookmark) {
            $bookmark->delete();
            $model->decrement('bookmarks_count');
            return $this->successResponse(['bookmarked' => false]);
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id' => $validated['bookmarkable_id'],
        ]);
        $model->increment('bookmarks_count');

        return $this->successResponse(['bookmarked' => true]);
    }

    public function check($type, $id, Request $request)
    {
        $exists = Bookmark::where([
            'user_id' => $request->user()->id,
            'bookmarkable_type' => match($type) {
                'news' => 'App\\Models\\News',
                'article' => 'App\\Models\\Article',
                default => '',
            },
            'bookmarkable_id' => $id,
        ])->exists();

        return $this->successResponse(['bookmarked' => $exists]);
    }

    public function destroy($id) { Bookmark::find($id)?->delete(); return $this->deletedResponse(); }
}
