<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tag;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\NewsResource;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    public function index()
    {
        $tags = Tag::active()->withContent()->orderBy('news_count', 'desc')->get();
        return $this->successResponse(TagResource::collection($tags));
    }

    public function trending()
    {
        $tags = Tag::trending()->active()->limit(10)->get();
        return $this->successResponse(TagResource::collection($tags));
    }

    public function popular()
    {
        $tags = Tag::active()->orderBy('news_count', 'desc')->limit(20)->get();
        return $this->successResponse(TagResource::collection($tags));
    }

    public function show($slug)
    {
        $tag = Tag::where('slug->ar', $slug)->orWhere('slug->en', $slug)->first();
        if (!$tag) return $this->notFoundResponse();
        return $this->successResponse(new TagResource($tag));
    }

    public function news($slug)
    {
        $tag = Tag::where('slug->ar', $slug)->orWhere('slug->en', $slug)->first();
        if (!$tag) return $this->notFoundResponse();
        $news = $tag->news()->published()->with('category')->latest()->paginate(15);
        return $this->paginatedResponse($news, null, NewsResource::class);
    }

    public function articles($slug)
    {
        $tag = Tag::where('slug->ar', $slug)->orWhere('slug->en', $slug)->first();
        if (!$tag) return $this->notFoundResponse();
        $articles = $tag->articles()->published()->with('writer')->latest()->paginate(12);
        return $this->paginatedResponse($articles, null, ArticleResource::class);
    }

    public function store(Request $request) { return $this->successResponse(null); }
    public function update(Request $request, $id) { return $this->successResponse(null); }
    public function destroy($id) { return $this->deletedResponse(); }
}
