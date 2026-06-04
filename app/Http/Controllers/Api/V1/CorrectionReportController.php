<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ContentCorrectionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CorrectionReportController extends BaseController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_type' => 'required|string|in:news,article,articles,report,reports,investigation,investigations,interview,interviews,page',
            'content_id' => 'nullable|integer',
            'content_title' => 'nullable|string|max:255',
            'url' => 'required|url|max:2048',
            'reason' => 'required|in:correction,source,image,typo,rights,other',
            'details' => 'required|string|min:20|max:4000',
            'evidence_url' => 'nullable|url|max:2048',
            'reporter_name' => 'nullable|string|max:120',
            'reporter_email' => 'nullable|email|max:255',
            'website' => 'nullable|size:0',
        ]);

        $modelClass = $this->modelClassFor($validated['content_type']);
        $contentId = Arr::get($validated, 'content_id');
        $reportable = $modelClass && $contentId ? $modelClass::query()->find($contentId) : null;

        $report = ContentCorrectionReport::create([
            'reportable_type' => $reportable ? $modelClass : null,
            'reportable_id' => $reportable?->id,
            'content_type' => $validated['content_type'],
            'content_title' => Arr::get($validated, 'content_title'),
            'url' => $validated['url'],
            'reason' => $validated['reason'],
            'details' => $validated['details'],
            'evidence_url' => Arr::get($validated, 'evidence_url'),
            'reporter_name' => Arr::get($validated, 'reporter_name'),
            'reporter_email' => Arr::get($validated, 'reporter_email'),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return $this->createdResponse([
            'id' => $report->id,
            'status' => $report->status,
        ], 'تم استلام البلاغ التحريري وسيتم مراجعته');
    }

    public function index(Request $request)
    {
        $query = ContentCorrectionReport::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        return $this->paginatedResponse($query->paginate(min((int) $request->get('per_page', 20), 50)));
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,resolved,rejected',
            'editor_notes' => 'nullable|string|max:4000',
        ]);

        $report = ContentCorrectionReport::query()->find($id);

        if (!$report) {
            return $this->notFoundResponse('البلاغ غير موجود');
        }

        $report->update([
            'status' => $validated['status'],
            'editor_notes' => Arr::get($validated, 'editor_notes'),
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        return $this->updatedResponse($report, 'تم تحديث حالة البلاغ');
    }

    private function modelClassFor(string $type): ?string
    {
        return match($type) {
            'news' => \App\Models\News::class,
            'article', 'articles' => \App\Models\Article::class,
            'report', 'reports' => \App\Models\Report::class,
            'investigation', 'investigations' => \App\Models\Investigation::class,
            'interview', 'interviews' => \App\Models\Interview::class,
            default => null,
        };
    }
}
