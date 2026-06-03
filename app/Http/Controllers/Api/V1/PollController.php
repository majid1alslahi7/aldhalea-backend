<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Poll;
use App\Http\Resources\PollResource;
use Illuminate\Http\Request;

class PollController extends BaseController
{
    public function index()
    {
        $polls = Poll::with('options')->latest()->paginate(10);
        return $this->paginatedResponse($polls);
    }

    public function active()
    {
        $polls = Poll::active()->with('options')->get();
        return $this->successResponse(PollResource::collection($polls));
    }

    public function featured()
    {
        $poll = Poll::active()->featured()->with('options')->latest()->first();
        return $poll ? $this->successResponse(new PollResource($poll)) : $this->successResponse(null);
    }

    public function show($id)
    {
        $poll = Poll::with('options')->find($id);
        if (!$poll) return $this->notFoundResponse();

        return $this->successResponse(new PollResource($poll));
    }

    public function results($id)
    {
        $poll = Poll::with('options')->find($id);
        if (!$poll) return $this->notFoundResponse();

        return $this->successResponse([
            'question' => $poll->question,
            'total_votes' => $poll->total_votes,
            'options' => $poll->options->map(fn($opt) => [
                'id' => $opt->id,
                'text' => $opt->text,
                'votes' => $opt->votes_count,
                'percentage' => $opt->percentage,
                'color' => $opt->color,
            ]),
        ]);
    }

    public function vote($id, Request $request)
    {
        $poll = Poll::active()->find($id);
        if (!$poll) return $this->errorResponse('الاستطلاع غير متاح');

        if ($poll->hasUserVoted($request->user()->id)) {
            return $this->errorResponse('لقد قمت بالتصويت مسبقاً');
        }

        $request->validate(['option_id' => 'required|exists:poll_options,id']);

        $option = $poll->options()->find($request->option_id);
        $option->increment('votes_count');

        $poll->votes()->create([
            'poll_option_id' => $option->id,
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
        ]);

        return $this->successResponse(null, 'تم التصويت بنجاح');
    }

    public function store(Request $request) { /* Admin CRUD */ }
    public function update(Request $request, $id) { /* Admin CRUD */ }
    public function destroy($id) { /* Admin CRUD */ }
}
