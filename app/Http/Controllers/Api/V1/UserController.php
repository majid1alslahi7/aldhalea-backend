<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function writers()
    {
        $writers = User::writers()->active()->get();
        return $this->successResponse($writers);
    }

    public function featuredWriters()
    {
        $writers = User::writers()->active()->limit(6)->get();
        return $this->successResponse($writers);
    }

    public function writerProfile($id)
    {
        $writer = User::writers()->find($id);
        if (!$writer) return $this->notFoundResponse();
        return $this->successResponse($writer);
    }

    public function writerArticles($id)
    {
        $articles = \App\Models\Article::published()->byWriter($id)->with('category')->latest()->paginate(12);
        return $this->paginatedResponse($articles, null, ArticleResource::class);
    }

    public function preferences(Request $request) { return $this->successResponse($request->user()->preferences); }
    public function updatePreferences(Request $request) { return $this->successResponse(null); }

    public function index() { return $this->successResponse(User::paginate(20)); }
    public function show($id) { return $this->successResponse(User::find($id)); }
    public function update(Request $request, $id) { return $this->successResponse(null); }
    public function updateRole(Request $request, $id) { return $this->successResponse(null); }
    public function toggleStatus($id) { return $this->successResponse(null); }
    public function destroy($id) { return $this->deletedResponse(); }
}
