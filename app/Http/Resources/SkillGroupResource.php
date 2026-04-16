<?php

namespace App\Http\Resources;

use App\Support\RequestLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\SkillGroup
 */
class SkillGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = RequestLocale::preferred($request);
        $useAr = $locale === 'ar' && $this->hasArabicCopy();

        $itemsEn = $this->buildItemListForLocale($useAr);

        return [
            'id' => (string) $this->id,
            'title' => $useAr ? (string) $this->title_ar : $this->title_en,
            'items' => $itemsEn,
            'titleEn' => $this->title_en,
            'titleAr' => $this->title_ar,
            'itemsEn' => $this->buildItemListForLocale(false),
            'itemsAr' => $this->items_ar !== null && $this->items_ar !== [] ? $this->items_ar : null,
            'sort_order' => $this->sort_order,
        ];
    }

    private function hasArabicCopy(): bool
    {
        return $this->title_ar !== null
            && $this->title_ar !== ''
            && $this->items_ar !== null
            && is_array($this->items_ar)
            && $this->items_ar !== [];
    }

    /**
     * @return list<array{name: string, image: string|null}>
     */
    private function buildItemListForLocale(bool $useAr): array
    {
        $enItems = $this->normalizeEnItems($this->items);
        if (! $useAr) {
            return $enItems;
        }

        $arRaw = $this->items_ar;
        if (! is_array($arRaw) || $arRaw === []) {
            return $enItems;
        }

        $out = [];
        foreach ($enItems as $i => $enItem) {
            $arName = $this->resolveArName($arRaw, $i);
            $name = $arName !== null && $arName !== '' ? $arName : $enItem['name'];
            $out[] = [
                'name' => $name,
                'image' => $enItem['image'],
            ];
        }

        return $out;
    }

    /**
     * @param  mixed  $arRaw
     */
    private function resolveArName(mixed $arRaw, int $i): ?string
    {
        if (! isset($arRaw[$i])) {
            return null;
        }
        $cell = $arRaw[$i];
        if (is_string($cell)) {
            return $cell;
        }
        if (is_array($cell) && isset($cell['name'])) {
            return (string) $cell['name'];
        }

        return null;
    }

    /**
     * @param  mixed  $raw
     * @return list<array{name: string, image: string|null}>
     */
    private function normalizeEnItems(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $out[] = ['name' => $item, 'image' => null];
            } elseif (is_array($item) && isset($item['name'])) {
                $out[] = [
                    'name' => (string) $item['name'],
                    'image' => $this->resolveImageUrl(
                        isset($item['image']) && is_string($item['image']) ? $item['image'] : null
                    ),
                ];
            }
        }

        return $out;
    }

    /**
     * Full absolute URL in JSON (same response as skill data) — no separate image route.
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
