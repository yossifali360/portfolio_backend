<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioCardResource;
use App\Models\PortfolioCard;
use App\Support\ExperienceContent;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'portfolioCards' => PortfolioCardResource::collection(
                PortfolioCard::query()->ordered()->get()
            )->resolve(),
            'experience' => ExperienceContent::entries(),
        ]);
    }
}
