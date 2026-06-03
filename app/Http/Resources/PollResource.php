<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'question' => $this->getTranslation('question', $locale),
            'description' => $this->getTranslation('description', $locale),
            'status' => $this->status,
            'is_active' => $this->is_active,
            'total_votes' => $this->total_votes,
            'allow_multiple' => (bool) $this->allow_multiple,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'options' => $this->whenLoaded('options', function() {
                return $this->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'text' => $opt->getTranslation('text', app()->getLocale()),
                    'color' => $opt->color,
                    'image' => $opt->image ? asset('storage/' . $opt->image) : null,
                    'votes_count' => $opt->votes_count,
                    'percentage' => $opt->percentage,
                ]);
            }),
        ];
    }
}
