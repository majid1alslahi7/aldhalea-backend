<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function index(Request $request)
    {
        $reports = Report::published()->with(['category', 'user'])->latest('published_at')->paginate(15);
        return $this->paginatedResponse($reports);
    }

    public function photoReports()
    {
        $reports = Report::published()->photoReports()->with('category')->latest()->paginate(15);
        return $this->paginatedResponse($reports);
    }

    public function videoReports()
    {
        $reports = Report::published()->videoReports()->with('category')->latest()->paginate(15);
        return $this->paginatedResponse($reports);
    }

    public function featured()
    {
        $reports = Report::published()->featured()->with('category')->latest()->limit(5)->get();
        return $this->successResponse($reports);
    }

    public function latest()
    {
        $reports = Report::recent()->with('category')->limit(10)->get();
        return $this->successResponse($reports);
    }

    public function show($slug)
    {
        $report = Report::where('slug->ar', $slug)->orWhere('slug->en', $slug)->with(['category', 'user'])->first();
        if (!$report) return $this->notFoundResponse();
        $report->increment('views_count');
        return $this->successResponse($report);
    }

    public function store(Request $request) { return $this->successResponse(null); }
    public function update(Request $request, $id) { return $this->successResponse(null); }
    public function destroy($id) { return $this->deletedResponse(); }
}
