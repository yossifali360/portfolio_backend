<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillGroupResource;
use App\Models\SkillGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SkillGroupController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return SkillGroupResource::collection(
            SkillGroup::query()->ordered()->get()
        );
    }

    public function show(SkillGroup $skillGroup): SkillGroupResource
    {
        return new SkillGroupResource($skillGroup);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->buildPayload($request, partial: false);
        $group = SkillGroup::query()->create($payload);

        return (new SkillGroupResource($group))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, SkillGroup $skillGroup): SkillGroupResource
    {
        $payload = $this->buildPayload($request, partial: true, existing: $skillGroup);
        if ($payload !== []) {
            $skillGroup->update($payload);
        }

        return new SkillGroupResource($skillGroup->fresh());
    }

    public function destroy(SkillGroup $skillGroup): Response
    {
        $this->deleteStoredSkillImages($skillGroup);
        $skillGroup->delete();

        return response()->noContent();
    }

    private function isMultipartSkillRequest(Request $request): bool
    {
        return str_contains((string) $request->header('Content-Type'), 'multipart/form-data');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(Request $request, bool $partial, ?SkillGroup $existing = null): array
    {
        if ($this->isMultipartSkillRequest($request)) {
            $data = $this->validatedMultipart($request, $partial);
        } else {
            $data = $this->validatedJson($request, $partial);
        }

        $payload = $this->toModelAttributes($data);
        if (isset($payload['items']) && is_array($payload['items'])) {
            $this->mergeItemIconUploads($request, $payload['items'], $existing);
            $payload['items'] = array_values($payload['items']);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedJson(Request $request, bool $partial): array
    {
        $optional = $partial ? 'sometimes|' : '';

        return $request->validate([
            'titleEn' => $optional.'required|string|max:255',
            'titleAr' => $optional.'nullable|string|max:255',
            'items' => $optional.'required|array|min:1',
            'items.*.name' => 'required|string|max:200',
            'items.*.image' => 'nullable|string|max:2048',
            'itemsAr' => $optional.'nullable|array',
            'itemsAr.*' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedMultipart(Request $request, bool $partial): array
    {
        $optional = $partial ? 'sometimes|' : '';

        /** @var array<string, mixed> $base */
        $base = $request->validate([
            'titleEn' => $optional.'required|string|max:255',
            'titleAr' => $optional.'nullable|string|max:255',
            'items' => $optional.'required|string',
            'itemsAr' => $optional.'nullable|string',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $itemsRaw = $base['items'] ?? null;
        if (! is_string($itemsRaw)) {
            throw ValidationException::withMessages(['items' => ['Items must be a JSON string.']]);
        }
        $decoded = json_decode($itemsRaw, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['items' => ['Items must be a JSON array.']]);
        }

        $items = $this->normalizeItemsFromDecoded($decoded);
        if ($items === []) {
            throw ValidationException::withMessages(['items' => ['Add at least one skill.']]);
        }

        $out = [
            'items' => $items,
        ];
        if (array_key_exists('titleEn', $base)) {
            $out['titleEn'] = $base['titleEn'];
        }
        if (array_key_exists('titleAr', $base)) {
            $out['titleAr'] = $base['titleAr'];
        }
        if (array_key_exists('sort_order', $base)) {
            $out['sort_order'] = $base['sort_order'];
        }

        $itemsArRaw = $base['itemsAr'] ?? null;
        if (is_string($itemsArRaw) && $itemsArRaw !== '') {
            $arDecoded = json_decode($itemsArRaw, true);
            if (! is_array($arDecoded)) {
                throw ValidationException::withMessages(['itemsAr' => ['itemsAr must be a JSON array.']]);
            }
            $out['itemsAr'] = array_values(array_map(
                static fn (mixed $v): ?string => is_string($v) ? $v : null,
                $arDecoded
            ));
        } elseif (array_key_exists('itemsAr', $base)) {
            $out['itemsAr'] = null;
        }

        return $out;
    }

    /**
     * @param  list<mixed>  $decoded
     * @return list<array{name: string, image: string|null}>
     */
    private function normalizeItemsFromDecoded(array $decoded): array
    {
        $out = [];
        foreach ($decoded as $row) {
            if (is_string($row)) {
                $out[] = ['name' => $row, 'image' => null];
            } elseif (is_array($row) && isset($row['name'])) {
                $img = $row['image'] ?? null;
                $out[] = [
                    'name' => (string) $row['name'],
                    'image' => is_string($img) && $img !== '' ? $img : null,
                ];
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toModelAttributes(array $data): array
    {
        $out = [];
        if (array_key_exists('titleEn', $data)) {
            $out['title_en'] = $data['titleEn'];
        }
        if (array_key_exists('titleAr', $data)) {
            $out['title_ar'] = $data['titleAr'] === '' || $data['titleAr'] === null
                ? null
                : $data['titleAr'];
        }
        if (array_key_exists('items', $data)) {
            $out['items'] = $this->normalizeItemsForStorage($data['items']);
        }
        if (array_key_exists('itemsAr', $data)) {
            $ar = $data['itemsAr'];
            $out['items_ar'] = is_array($ar) && $ar !== [] ? array_values($ar) : null;
        }
        if (array_key_exists('sort_order', $data)) {
            $out['sort_order'] = $data['sort_order'];
        }

        return $out;
    }

    /**
     * @param  mixed  $items
     * @return list<array{name: string, image: string|null}>
     */
    private function normalizeItemsForStorage(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $row) {
            if (is_string($row)) {
                $out[] = ['name' => $row, 'image' => null];
            } elseif (is_array($row) && isset($row['name'])) {
                $img = $row['image'] ?? null;
                $out[] = [
                    'name' => (string) $row['name'],
                    'image' => is_string($img) && $img !== '' ? $img : null,
                ];
            }
        }

        return $out;
    }

    /**
     * @param  list<array{name: string, image: string|null}>  $items
     */
    private function mergeItemIconUploads(Request $request, array &$items, ?SkillGroup $existing = null): void
    {
        $clearRaw = $request->input('clear_item_image');
        $clear = [];
        if (is_string($clearRaw) && $clearRaw !== '') {
            $decoded = json_decode($clearRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $i) {
                    if (is_int($i) || (is_string($i) && ctype_digit((string) $i))) {
                        $clear[(int) $i] = true;
                    }
                }
            }
        }

        foreach ($items as $i => &$row) {
            $key = 'item_image_'.$i;
            if ($request->hasFile($key)) {
                $old = $this->itemImageAt($existing, $i);
                $this->deleteStoredSkillImageIfManaged($old);
                $row['image'] = $request->file($key)->store('skills', 'public');
            } elseif (isset($clear[$i]) && $clear[$i]) {
                $old = $this->itemImageAt($existing, $i);
                $this->deleteStoredSkillImageIfManaged($old);
                $row['image'] = null;
            }
        }
        unset($row);
    }

    private function itemImageAt(?SkillGroup $existing, int $i): ?string
    {
        if ($existing === null) {
            return null;
        }
        $items = $existing->items;
        if (! is_array($items) || ! isset($items[$i]) || ! is_array($items[$i])) {
            return null;
        }

        $img = $items[$i]['image'] ?? null;

        return is_string($img) ? $img : null;
    }

    private function deleteStoredSkillImages(SkillGroup $group): void
    {
        $items = $group->items;
        if (! is_array($items)) {
            return;
        }
        foreach ($items as $row) {
            if (is_array($row) && isset($row['image'])) {
                $this->deleteStoredSkillImageIfManaged(is_string($row['image']) ? $row['image'] : null);
            }
        }
    }

    private function deleteStoredSkillImageIfManaged(?string $image): void
    {
        if ($image === null || $image === '') {
            return;
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return;
        }
        if (str_starts_with($image, '/')) {
            return;
        }
        Storage::disk('public')->delete($image);
    }
}
