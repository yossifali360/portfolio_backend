<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationEntryResource;
use App\Http\Resources\PortfolioCardResource;
use App\Http\Resources\SkillGroupResource;
use App\Models\EducationEntry;
use App\Models\PortfolioCard;
use App\Models\SkillGroup;
use App\Support\ExperienceContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'portfolioCards' => PortfolioCardResource::collection(
                PortfolioCard::query()->ordered()->get()
            )->resolve($request),
            'experience' => ExperienceContent::entries(),
            'skillGroups' => SkillGroupResource::collection(
                SkillGroup::query()->ordered()->get()
            )->resolve($request),
            'educationEntries' => EducationEntryResource::collection(
                EducationEntry::query()->ordered()->get()
            )->resolve($request),
        ]);
    }
}
