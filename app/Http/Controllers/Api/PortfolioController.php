<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioCardResource;
use App\Models\PortfolioCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
        $validated = $this->validated($request);

        $card = PortfolioCard::query()->create($validated);

        return (new PortfolioCardResource($card))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, PortfolioCard $portfolioCard): PortfolioCardResource
    {
        $validated = $this->validated($request, partial: true);

        $portfolioCard->update($validated);

        return new PortfolioCardResource($portfolioCard->fresh());
    }

    public function destroy(PortfolioCard $portfolioCard): Response
    {
        $portfolioCard->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial = false): array
    {
        $optional = $partial ? 'sometimes|' : '';

        /** @var array<string, mixed> $data */
        $data = $request->validate([
            'title' => $optional.'required|string|max:255',
            'subtitle' => $optional.'required|string|max:255',
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
}
