<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioCardResource;
use App\Models\PortfolioCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PortfolioController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PortfolioCardResource::collection(
            PortfolioCard::query()->ordered()->get()
        );
    }

    public function show(PortfolioCard $portfolioCard): PortfolioCardResource
    {
        return new PortfolioCardResource($portfolioCard);
    }

    public function store(Request $request): JsonResponse
    {
        if ($this->isMultipartPortfolioRequest($request)) {
            $validated = $this->validatedFromMultipart($request, partial: false);
        } else {
            $validated = $this->validatedFromJson($request, partial: false);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('portfolio', 'public');
        }

        $card = PortfolioCard::query()->create($validated);

        return (new PortfolioCardResource($card))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, PortfolioCard $portfolioCard): PortfolioCardResource
    {
        if ($this->isMultipartPortfolioRequest($request)) {
            $validated = $this->validatedFromMultipart($request, partial: true);
        } else {
            $validated = $this->validatedFromJson($request, partial: true);
        }

        if ($request->hasFile('image')) {
            $this->deleteStoredImageIfManaged($portfolioCard->image);
            $validated['image'] = $request->file('image')->store('portfolio', 'public');
        } elseif ($request->boolean('clear_image')) {
            $this->deleteStoredImageIfManaged($portfolioCard->image);
            $validated['image'] = null;
        }

        if ($validated !== []) {
            $portfolioCard->update($validated);
        }

        return new PortfolioCardResource($portfolioCard->fresh());
    }

    public function destroy(PortfolioCard $portfolioCard): Response
    {
        $this->deleteStoredImageIfManaged($portfolioCard->image);
        $portfolioCard->delete();

        return response()->noContent();
    }

    private function isMultipartPortfolioRequest(Request $request): bool
    {
        return str_contains((string) $request->header('Content-Type'), 'multipart/form-data');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFromJson(Request $request, bool $partial): array
    {
        $optional = $partial ? 'sometimes|' : '';

        /** @var array<string, mixed> $data */
        $data = $request->validate([
            'title' => $optional.'required|string|max:255',
            'subtitle' => $optional.'nullable|string|max:255',
            'description' => $optional.'required|string',
            'tags' => $optional.'required|array',
            'tags.*' => 'string|max:100',
            'accentColor' => $optional.'required|string|max:32',
            'image' => 'nullable|string|max:500',
            'link' => 'nullable|string|max:2048',
            'links' => 'nullable|array',
            'links.*.href' => 'required|string|max:2048',
            'links.*.labelKey' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);

        if (array_key_exists('accentColor', $data)) {
            $data['accent_color'] = $data['accentColor'];
            unset($data['accentColor']);
        }

        return $data;
    }

    /**
     * Multipart body: `tags` and `links` are JSON strings; optional `image` file; optional `clear_image` (boolean).
     *
     * @return array<string, mixed>
     */
    private function validatedFromMultipart(Request $request, bool $partial): array
    {
        $optional = $partial ? 'sometimes|' : '';

        $rules = [
            'title' => $optional.'required|string|max:255',
            'subtitle' => $optional.'nullable|string|max:255',
            'description' => $optional.'required|string',
            'accentColor' => $optional.'required|string|max:32',
            'link' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'tags' => $optional.'required|string',
            'links' => 'nullable|string',
            'clear_image' => 'sometimes|boolean',
        ];

        if ($request->hasFile('image')) {
            $rules['image'] = ($partial ? 'sometimes|' : '').'required|image|mimes:jpeg,jpg,png,gif,webp|max:8192';
        }

        /** @var array<string, mixed> $data */
        $data = $request->validate($rules);

        $tagsRaw = $data['tags'] ?? null;
        if (! is_string($tagsRaw)) {
            throw ValidationException::withMessages(['tags' => ['Tags must be a JSON array string.']]);
        }
        $data['tags'] = $this->decodeTagsJson($tagsRaw);

        $linksRaw = $data['links'] ?? '';
        unset($data['links']);
        $data['links'] = is_string($linksRaw) && $linksRaw !== ''
            ? $this->decodeLinksJson($linksRaw)
            : null;

        unset($data['clear_image']);

        if (array_key_exists('accentColor', $data)) {
            $data['accent_color'] = $data['accentColor'];
            unset($data['accentColor']);
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    private function decodeTagsJson(string $tags): array
    {
        $decoded = json_decode($tags, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['tags' => ['Tags must be a JSON array.']]);
        }

        Validator::make(
            ['tags' => $decoded],
            ['tags' => 'required|array', 'tags.*' => 'string|max:100']
        )->validate();

        return array_values(array_map(static fn (mixed $t): string => (string) $t, $decoded));
    }

    /**
     * @return list<array{href: string, labelKey: string}>|null
     */
    private function decodeLinksJson(string $links): ?array
    {
        $decoded = json_decode($links, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages(['links' => ['Links must be valid JSON.']]);
        }
        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        Validator::make(
            ['links' => $decoded],
            [
                'links' => 'required|array',
                'links.*.href' => 'required|string|max:2048',
                'links.*.labelKey' => 'required|string|max:255',
            ]
        )->validate();

        return $decoded;
    }

    private function deleteStoredImageIfManaged(?string $image): void
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
