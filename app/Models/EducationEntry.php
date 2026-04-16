<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EducationEntry extends Model
{
    protected $fillable = [
        'institution_en',
        'institution_ar',
        'degree_en',
        'degree_ar',
        'period',
        'detail_en',
        'detail_ar',
        'overall_grade_en',
        'overall_grade_ar',
        'sort_order',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
