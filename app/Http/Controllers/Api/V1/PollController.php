<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Poll;
use App\Http\Resources\PollResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PollController extends BaseController
{
    public function index()
    {
        $polls = Poll::with('options')->latest()->paginate(10);
        return $this->paginatedResponse($polls, null, PollResource::class);
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

        $validated = $request->validate([
            'option_id' => [
                'required',
                Rule::exists('poll_options', 'id')->where('poll_id', $poll->id),
            ],
        ]);

        $user = $request->user();
        $ipAddress = $request->ip();

        $alreadyVoted = $user
            ? $poll->votes()->where('user_id', $user->id)->exists()
            : $poll->votes()->whereNull('user_id')->where('ip_address', $ipAddress)->exists();

        if (!$poll->allow_multiple && $alreadyVoted) {
            return $this->errorResponse('لقد قمت بالتصويت مسبقاً');
        }

        $poll = DB::transaction(function () use ($poll, $validated, $user, $request, $ipAddress) {
            $option = $poll->options()->lockForUpdate()->findOrFail($validated['option_id']);
            $option->increment('votes_count');

            $poll->votes()->create([
                'poll_option_id' => $option->id,
                'user_id' => $user?->id,
                'ip_address' => $ipAddress,
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);

            return $poll->fresh('options');
        });

        return $this->successResponse(new PollResource($poll), 'تم التصويت بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $poll = DB::transaction(function () use ($validated, $request) {
            $options = $validated['options'];
            unset($validated['options']);

            $poll = Poll::create(array_merge($validated, [
                'user_id' => $request->user()->id,
            ]));

            foreach ($options as $index => $option) {
                $poll->options()->create([
                    'text' => $option['text'],
                    'color' => $option['color'] ?? null,
                    'image' => $option['image'] ?? null,
                    'order' => $option['order'] ?? $index + 1,
                ]);
            }

            return $poll->fresh('options');
        });

        return $this->createdResponse(new PollResource($poll), 'تم إنشاء الاستطلاع');
    }

    public function update(Request $request, $id)
    {
        $poll = Poll::with('options')->find($id);
        if (!$poll) return $this->notFoundResponse();

        $validated = $request->validate($this->rules(true));

        $poll = DB::transaction(function () use ($poll, $validated) {
            $options = $validated['options'] ?? null;
            unset($validated['options']);

            $poll->update($validated);

            if ($options !== null) {
                $keptOptionIds = [];
                foreach ($options as $index => $option) {
                    $payload = [
                        'text' => $option['text'],
                        'color' => $option['color'] ?? null,
                        'image' => $option['image'] ?? null,
                        'order' => $option['order'] ?? $index + 1,
                    ];

                    if (!empty($option['id'])) {
                        $poll->options()->whereKey($option['id'])->update($payload);
                        $keptOptionIds[] = $option['id'];
                    } else {
                        $keptOptionIds[] = $poll->options()->create($payload)->id;
                    }
                }

                $poll->options()->whereNotIn('id', $keptOptionIds)->delete();
            }

            return $poll->fresh('options');
        });

        return $this->updatedResponse(new PollResource($poll), 'تم تحديث الاستطلاع');
    }

    public function destroy($id)
    {
        $poll = Poll::find($id);
        if (!$poll) return $this->notFoundResponse();

        $poll->delete();
        return $this->deletedResponse('تم حذف الاستطلاع');
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'question.ar' => "{$required}|string|max:255",
            'question.en' => 'nullable|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'status' => ($partial ? 'sometimes|' : '') . 'in:draft,active,closed',
            'is_featured' => 'boolean',
            'allow_multiple' => 'boolean',
            'show_results' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'options' => ($partial ? 'nullable' : 'required') . '|array|min:2',
            'options.*.id' => 'nullable|integer|exists:poll_options,id',
            'options.*.text.ar' => 'required_with:options|string|max:255',
            'options.*.text.en' => 'nullable|string|max:255',
            'options.*.color' => 'nullable|string|max:20',
            'options.*.image' => 'nullable|string|max:255',
            'options.*.order' => 'nullable|integer|min:0',
        ];
    }
}
