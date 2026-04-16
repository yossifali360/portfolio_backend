<?php

namespace App\Http\Resources;

use App\Support\RequestLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\EducationEntry
 */
class EducationEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = RequestLocale::preferred($request);
        $useAr = $locale === 'ar' && $this->hasArabicCopy();

        return [
            'id' => (string) $this->id,
            'institution' => $useAr ? (string) $this->institution_ar : $this->institution_en,
            'degree' => $useAr ? (string) $this->degree_ar : $this->degree_en,
            'period' => $this->period,
            'detail' => $useAr
                ? $this->nullableString($this->detail_ar ?? $this->detail_en)
                : $this->nullableString($this->detail_en),
            'overallGrade' => $useAr
                ? $this->nullableString($this->overall_grade_ar ?? $this->overall_grade_en)
                : $this->nullableString($this->overall_grade_en),
            'institutionEn' => $this->institution_en,
            'institutionAr' => $this->institution_ar,
            'degreeEn' => $this->degree_en,
            'degreeAr' => $this->degree_ar,
            'detailEn' => $this->detail_en,
            'detailAr' => $this->detail_ar,
            'overallGradeEn' => $this->overall_grade_en,
            'overallGradeAr' => $this->overall_grade_ar,
            'sort_order' => $this->sort_order,
        ];
    }

    private function hasArabicCopy(): bool
    {
        return $this->institution_ar !== null
            && $this->institution_ar !== ''
            && $this->degree_ar !== null
            && $this->degree_ar !== '';
    }

    private function nullableString(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }

        return $v;
    }
}
