<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationEntryResource;
use App\Models\EducationEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EducationEntryController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return EducationEntryResource::collection(
            EducationEntry::query()->ordered()->get()
        );
    }

    public function show(EducationEntry $educationEntry): EducationEntryResource
    {
        return new EducationEntryResource($educationEntry);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, partial: false);
        $entry = EducationEntry::query()->create($data);

        return (new EducationEntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, EducationEntry $educationEntry): EducationEntryResource
    {
        $data = $this->validated($request, partial: true);
        if ($data !== []) {
            $educationEntry->update($data);
        }

        return new EducationEntryResource($educationEntry->fresh());
    }

    public function destroy(EducationEntry $educationEntry): Response
    {
        $educationEntry->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial): array
    {
        $optional = $partial ? 'sometimes|' : '';

        /** @var array<string, mixed> $data */
        $data = $request->validate([
            'institutionEn' => $optional.'required|string|max:255',
            'institutionAr' => $optional.'nullable|string|max:255',
            'degreeEn' => $optional.'required|string|max:500',
            'degreeAr' => $optional.'nullable|string|max:500',
            'period' => $optional.'required|string|max:100',
            'detailEn' => $optional.'nullable|string',
            'detailAr' => $optional.'nullable|string',
            'overallGradeEn' => $optional.'nullable|string',
            'overallGradeAr' => $optional.'nullable|string',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $out = [];
        if (array_key_exists('institutionEn', $data)) {
            $out['institution_en'] = $data['institutionEn'];
        }
        if (array_key_exists('institutionAr', $data)) {
            $out['institution_ar'] = self::emptyToNull($data['institutionAr']);
        }
        if (array_key_exists('degreeEn', $data)) {
            $out['degree_en'] = $data['degreeEn'];
        }
        if (array_key_exists('degreeAr', $data)) {
            $out['degree_ar'] = self::emptyToNull($data['degreeAr']);
        }
        if (array_key_exists('period', $data)) {
            $out['period'] = $data['period'];
        }
        if (array_key_exists('detailEn', $data)) {
            $out['detail_en'] = self::emptyToNull($data['detailEn']);
        }
        if (array_key_exists('detailAr', $data)) {
            $out['detail_ar'] = self::emptyToNull($data['detailAr']);
        }
        if (array_key_exists('overallGradeEn', $data)) {
            $out['overall_grade_en'] = self::emptyToNull($data['overallGradeEn']);
        }
        if (array_key_exists('overallGradeAr', $data)) {
            $out['overall_grade_ar'] = self::emptyToNull($data['overallGradeAr']);
        }
        if (array_key_exists('sort_order', $data)) {
            $out['sort_order'] = $data['sort_order'];
        }

        return $out;
    }

    private static function emptyToNull(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }

        return (string) $v;
    }
}
