<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Interview;
use Illuminate\Http\Request;

class InterviewController extends BaseController
{
    public function index()
    {
        $interviews = Interview::published()->with(['category', 'interviewer'])->latest('published_at')->paginate(12);
        return $this->paginatedResponse($interviews);
    }

    public function video()
    {
        $interviews = Interview::published()->videoInterviews()->with('category')->latest()->paginate(12);
        return $this->paginatedResponse($interviews);
    }

    public function podcast()
    {
        $interviews = Interview::published()->podcastInterviews()->with('category')->latest()->paginate(12);
        return $this->paginatedResponse($interviews);
    }

    public function featured()
    {
        $interviews = Interview::published()->featured()->with('category')->latest()->limit(5)->get();
        return $this->successResponse($interviews);
    }

    public function latest()
    {
        $interviews = Interview::recent()->with('category')->limit(10)->get();
        return $this->successResponse($interviews);
    }

    public function show($slug)
    {
        $interview = Interview::where('slug->ar', $slug)->orWhere('slug->en', $slug)->with(['category', 'interviewer'])->first();
        if (!$interview) return $this->notFoundResponse();
        $interview->increment('views_count');
        return $this->successResponse($interview);
    }

    public function store(Request $request) { return $this->successResponse(null); }
    public function update(Request $request, $id) { return $this->successResponse(null); }
    public function destroy($id) { return $this->deletedResponse(); }
}
