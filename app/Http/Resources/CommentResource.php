<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user_name,
                'avatar' => $this->user_avatar,
            ],
            'parent_id' => $this->parent_id,
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->replies_count,
            'likes_count' => $this->likes_count,
            'is_pinned' => (bool) $this->is_pinned,
            'is_edited' => (bool) $this->is_edited,
            'status' => $this->status,
            'time_ago' => $this->time_ago,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
