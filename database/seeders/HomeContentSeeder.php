<?php

namespace Database\Seeders;

use App\Models\EducationEntry;
use App\Models\SkillGroup;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        if (SkillGroup::query()->exists() || EducationEntry::query()->exists()) {
            return;
        }

        $groups = [
            [
                'title_en' => 'Languages & core',
                'title_ar' => null,
                'items' => [
                    ['name' => 'React', 'image' => null],
                    ['name' => 'Next.js', 'image' => null],
                    ['name' => 'TypeScript', 'image' => null],
                    ['name' => 'JavaScript (ES6+)', 'image' => null],
                    ['name' => 'HTML5', 'image' => null],
                    ['name' => 'CSS3', 'image' => null],
                    ['name' => 'Jest', 'image' => null],
                ],
                'items_ar' => null,
                'sort_order' => 1,
            ],
            [
                'title_en' => 'State, data & UI',
                'title_ar' => null,
                'items' => [
                    ['name' => 'Zustand', 'image' => null],
                    ['name' => 'TanStack Query', 'image' => null],
                    ['name' => 'Redux', 'image' => null],
                    ['name' => 'Tailwind CSS', 'image' => null],
                    ['name' => 'Sass / SCSS', 'image' => null],
                ],
                'items_ar' => null,
                'sort_order' => 2,
            ],
            [
                'title_en' => 'Tools & delivery',
                'title_ar' => null,
                'items' => [
                    ['name' => 'Git', 'image' => null],
                    ['name' => 'REST APIs', 'image' => null],
                    ['name' => 'Headless CMS', 'image' => null],
                    ['name' => 'Core Web Vitals', 'image' => null],
                    ['name' => 'Responsive UI', 'image' => null],
                ],
                'items_ar' => null,
                'sort_order' => 3,
            ],
        ];

        foreach ($groups as $row) {
            SkillGroup::query()->create($row);
        }

        EducationEntry::query()->create([
            'institution_en' => 'Helwan University',
            'institution_ar' => null,
            'degree_en' => 'Bachelor’s in Business Information Systems',
            'degree_ar' => null,
            'period' => '09/2020 — 06/2024',
            'detail_en' => 'GPA: 3.93.',
            'detail_ar' => null,
            'overall_grade_en' => 'Overall grade: Excellent with honors.',
            'overall_grade_ar' => null,
            'sort_order' => 1,
        ]);
    }
}
