<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BreakingNews;
use Illuminate\Http\Request;

class BreakingNewsController extends BaseController
{
    public function active()
    {
        $breaking = BreakingNews::active()->latest()->limit(5)->get();
        return $this->successResponse($breaking);
    }

    public function store(Request $request) { return $this->successResponse(null); }
    public function update(Request $request, $id) { return $this->successResponse(null); }
    public function destroy($id) { return $this->deletedResponse(); }
}
