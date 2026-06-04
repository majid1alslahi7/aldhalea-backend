<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShareController extends BaseController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shareable_type' => 'required|string|in:news,article,articles,report,reports,investigation,investigations,interview,interviews',
            'shareable_id' => 'required|integer',
            'platform' => 'required|in:facebook,twitter,x,whatsapp,telegram,email,copy_link,native,other',
            'url' => 'nullable|url|max:2048',
        ]);

        $modelClass = $this->modelClassFor($validated['shareable_type']);
        $shareable = $modelClass::query()->find($validated['shareable_id']);

        if (!$shareable) {
            return $this->notFoundResponse('المحتوى غير موجود');
        }

        $share = Share::create([
            'user_id' => $request->user()?->id,
            'shareable_type' => $modelClass,
            'shareable_id' => $validated['shareable_id'],
            'platform' => $validated['platform'],
            'url' => Arr::get($validated, 'url'),
            'ip_address' => $request->ip(),
            'referer' => substr((string) $request->headers->get('referer'), 0, 2048),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        if (array_key_exists('shares_count', $shareable->getAttributes())) {
            $shareable->increment('shares_count');
        }

        return $this->createdResponse([
            'id' => $share->id,
            'shares_count' => $shareable->fresh()?->shares_count,
        ], 'تم تسجيل المشاركة');
    }

    public function myShares(Request $request)
    {
        $shares = Share::where('user_id', $request->user()->id)->latest()->paginate(20);
        return $this->paginatedResponse($shares);
    }

    private function modelClassFor(string $type): string
    {
        return match($type) {
            'news' => \App\Models\News::class,
            'article', 'articles' => \App\Models\Article::class,
            'report', 'reports' => \App\Models\Report::class,
            'investigation', 'investigations' => \App\Models\Investigation::class,
            'interview', 'interviews' => \App\Models\Interview::class,
        };
    }
}
