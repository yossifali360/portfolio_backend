<?php

namespace App\Support;

/**
 * Experience entries JSON shape matches my-app/types/experience.ts (ExperienceEntry).
 */
final class ExperienceContent
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function entries(): array
    {
        return [
            [
                'id' => '1',
                'title' => 'Front-End Engineer',
                'company' => 'Parallel',
                'period' => '12/2024 – present',
                'description' => "Architected fully dynamic, CMS-driven web applications using Next.js and TypeScript, where core UI components, navigation, and page layouts are managed via a Headless CMS.\n\nEngineered scalable UI libraries and optimized Core Web Vitals for enterprise-scale platforms.\n\nIntegrated complex APIs with strict accessibility and clean code standards in an agile team.",
                'tags' => ['Next.js', 'TypeScript', 'Headless CMS', 'Core Web Vitals', 'SEO'],
            ],
            [
                'id' => '2',
                'title' => 'Front-End Developer',
                'company' => 'Uncle J',
                'period' => '06/2024 – 12/2024',
                'description' => "Developed and optimized responsive web applications using React. Built scalable UI components and seamless API integrations.\n\nEnsured high standards of accessibility (WCAG) and code efficiency to enhance performance and user experience.",
                'tags' => ['React', 'REST APIs', 'Performance'],
            ],
            [
                'id' => '3',
                'title' => 'Front-End Developer',
                'company' => 'Eraasoft',
                'period' => '02/2024 – 05/2024',
                'description' => "Developed and scaled engaging web applications using React with advanced front-end architectures.\n\nDelivered intuitive UI/UX and implementations that achieved high client satisfaction.",
                'tags' => ['React', 'UI/UX', 'Architecture'],
            ],
        ];
    }
}
