<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CitizenReport;
use Illuminate\Http\Request;

class CitizenReportController extends BaseController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'reporter_name' => 'nullable|string',
            'reporter_email' => 'nullable|email',
            'reporter_phone' => 'nullable|string',
        ]);

        $report = CitizenReport::create(array_merge($validated, [
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
        ]));

        return $this->createdResponse($report, 'تم استلام بلاغك');
    }

    public function myReports(Request $request)
    {
        return $this->successResponse(CitizenReport::where('user_id', $request->user()->id)->latest()->paginate(10));
    }

    public function show($id) { return $this->successResponse(CitizenReport::find($id)); }
    public function all() { return $this->successResponse(CitizenReport::latest()->paginate(20)); }
    public function review(Request $request, $id) { return $this->successResponse(null); }
    public function approve($id) { return $this->successResponse(null); }
}
