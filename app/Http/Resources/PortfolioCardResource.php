<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'image' => $this->resolveImageUrl($this->image),
            'link' => $this->link,
            'links' => $this->links,
        ];
    }

    /**
     * Full absolute URL in JSON (same response as card data) — no separate image route.
     */
    private function resolveImageUrl(?string $image): ?string
    {
        if ($image === null || $image === '') {
            return null;
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        if (str_starts_with($image, '/')) {
            return url($image);
        }

        return url(Storage::disk('public')->url($image));
    }
}
