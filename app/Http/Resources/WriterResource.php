<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WriterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar' => $this->avatar_url,
            'bio' => $this->bio,
            'role' => $this->role,
            'total_articles' => $this->when($this->total_articles, $this->total_articles),
            'total_views' => $this->when($this->total_views, $this->total_views),
            'social_links' => $this->when($this->social_links, $this->social_links),
        ];
    }
}
