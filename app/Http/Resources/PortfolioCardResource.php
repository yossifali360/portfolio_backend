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

    private function resolveImageUrl(?string $image): ?string
    {
        if ($image === null || $image === '') {
            return null;
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }
        if (str_starts_with($image, '/storage/')) {
            return url($image);
        }
        if (str_starts_with($image, '/')) {
            return $image;
        }

        // Stored relative path on the public disk (e.g. portfolio/xxx.png) — use API
        // route so images work when /storage/... is blocked (403) on the host.
        if (str_starts_with($image, 'portfolio/')) {
            $filename = substr($image, strlen('portfolio/'));

            return url('/api/portfolio-files/'.$filename);
        }

        return url(Storage::disk('public')->url($image));
    }
}
