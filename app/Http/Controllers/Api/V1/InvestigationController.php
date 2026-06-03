<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Investigation;
use Illuminate\Http\Request;

class InvestigationController extends BaseController
{
    public function index()
    {
        $investigations = Investigation::published()->with(['category', 'user'])->latest('published_at')->paginate(12);
        return $this->paginatedResponse($investigations);
    }

    public function featured()
    {
        $investigations = Investigation::published()->featured()->with('category')->latest()->limit(5)->get();
        return $this->successResponse($investigations);
    }

    public function urgent()
    {
        $investigations = Investigation::published()->urgent()->with('category')->latest()->limit(5)->get();
        return $this->successResponse($investigations);
    }

    public function latest()
    {
        $investigations = Investigation::recent()->with('category')->limit(10)->get();
        return $this->successResponse($investigations);
    }

    public function show($slug)
    {
        $investigation = Investigation::where('slug->ar', $slug)->orWhere('slug->en', $slug)->with(['category', 'user'])->first();
        if (!$investigation) return $this->notFoundResponse();
        $investigation->increment('views_count');
        return $this->successResponse($investigation);
    }

    public function store(Request $request) { return $this->successResponse(null); }
    public function update(Request $request, $id) { return $this->successResponse(null); }
    public function destroy($id) { return $this->deletedResponse(); }
}
