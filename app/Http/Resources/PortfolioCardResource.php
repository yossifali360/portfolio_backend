<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PortfolioCard
 */
class PortfolioCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'tags' => $this->tags ?? [],
            'accentColor' => $this->accent_color,
            'image' => $this->image,
            'link' => $this->link,
            'links' => $this->links,
        ];
    }
}
