<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Share;
use Illuminate\Http\Request;

class ShareController extends BaseController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shareable_type' => 'required|string',
            'shareable_id' => 'required|integer',
            'platform' => 'required|in:facebook,twitter,whatsapp,telegram,email,copy_link,other',
        ]);

        Share::create([
            'user_id' => $request->user()?->id,
            'shareable_type' => 'App\\Models\\' . ucfirst($validated['shareable_type']),
            'shareable_id' => $validated['shareable_id'],
            'platform' => $validated['platform'],
            'ip_address' => $request->ip(),
        ]);

        return $this->createdResponse(null, 'تم تسجيل المشاركة');
    }

    public function myShares(Request $request)
    {
        $shares = Share::where('user_id', $request->user()->id)->latest()->paginate(20);
        return $this->paginatedResponse($shares);
    }
}
