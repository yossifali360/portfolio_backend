<?php

namespace Database\Seeders;

use App\Models\PortfolioCard;
use Illuminate\Database\Seeder;

class PortfolioCardSeeder extends Seeder
{
    public function run(): void
    {
        if (PortfolioCard::query()->exists()) {
            return;
        }

        $rows = [
            [
                'title' => 'AutoConnect',
                'subtitle' => 'SaaS · Multi-tenant',
                'description' => 'Multi-tenant SaaS admin panel with modular CMS, inventory, and financial workflows. RBAC for dealership tiers. Data-heavy modules for orders, offers, and localized content in React and TypeScript.',
                'tags' => ['React', 'TypeScript', 'RBAC', 'SaaS'],
                'accent_color' => '#6366f1',
                'sort_order' => 1,
            ],
            [
                'title' => 'Hyundai',
                'subtitle' => 'Saudi Arabia & Middle East',
                'description' => 'Reusable responsive UI components and key sections across multiple pages. Payment integration and performance improvements through rendering and layout optimizations.',
                'tags' => ['Next.js', 'Payments', 'Performance'],
                'image' => '/images/hyundai.png',
                'links' => [
                    ['href' => 'https://hyundaiksa.com/en', 'labelKey' => 'regionKsa'],
                    ['href' => 'https://hyundai-me.com/en', 'labelKey' => 'regionMiddleEast'],
                ],
                'accent_color' => '#0ea5e9',
                'sort_order' => 2,
            ],
            [
                'title' => 'Lexus',
                'subtitle' => 'Kuwait',
                'description' => 'Full-scale delivery with responsive pages and reusable UI components. SEO and UX improvements for visibility and a smooth user journey on a scalable, secure architecture.',
                'tags' => ['React', 'SEO', 'UX'],
                'image' => '/images/lexus.png',
                'link' => 'https://www.lexus.com.kw/en',
                'accent_color' => '#a855f7',
                'sort_order' => 3,
            ],
            [
                'title' => 'MadinetMasr',
                'subtitle' => 'Growth pages',
                'description' => 'Growth main and inner pages with reusable components and responsive layouts aligned with brand guidelines. Performance and UI structure tuned for a seamless experience.',
                'tags' => ['React', 'Responsive', 'Brand'],
                'image' => '/images/madinetMasr.png',
                'link' => 'https://madinetmasr.com/en',
                'accent_color' => '#10b981',
                'sort_order' => 4,
            ],
            [
                'title' => 'Tatweer Misr',
                'subtitle' => 'UI enhancements',
                'description' => 'UI improvements across multiple pages: design consistency, layout fixes, and performance optimizations for a stronger overall experience.',
                'tags' => ['React', 'UI', 'Performance'],
                'image' => '/images/tatweerMisr.png',
                'link' => 'https://tatweermisr.com/',
                'accent_color' => '#f59e0b',
                'sort_order' => 5,
            ],
            [
                'title' => 'Ford',
                'subtitle' => 'Enterprise platform',
                'description' => 'Reusable components and key flows: Payment (down, full, financing), Trims, Thank-You. Full API integration, localization, and responsive branding across devices.',
                'tags' => ['React', 'APIs', 'i18n'],
                'accent_color' => '#2563eb',
                'sort_order' => 6,
            ],
            [
                'title' => 'LMD',
                'subtitle' => 'Full project delivery',
                'description' => 'Responsive interfaces and reusable components with API integrations. SEO and UX best practices for performance, visibility, and engagement on a secure, scalable stack.',
                'tags' => ['React', 'SEO', 'APIs'],
                'image' => '/images/lmd.png',
                'link' => 'https://www.lmd.com.eg/',
                'accent_color' => '#ec4899',
                'sort_order' => 7,
            ],
        ];

        foreach ($rows as $row) {
            PortfolioCard::query()->create($row);
        }
    }
}
