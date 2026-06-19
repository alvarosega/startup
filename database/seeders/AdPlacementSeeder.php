<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdPlacement;
use Illuminate\Database\Seeder;

class AdPlacementSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            ['name' => 'Brand Banner Hero', 'code' => 'BRAND_HERO', 'max_items' => 5],
            ['name' => 'Brand Category Top', 'code' => 'CATEGORY_HERO', 'max_items' => 1],
            ['name' => 'Brand Bundles Grid', 'code' => 'BUNDLE_HERO', 'max_items' => 1],
            ['name' => 'Resultados Búsqueda Patrocinados', 'code' => 'SEARCH_TOP', 'max_items' => 2],
        ];

        foreach ($placements as $p) {
            AdPlacement::updateOrCreate(
                ['code' => $p['code']],
                [
                    'name'      => $p['name'],
                    'max_items' => $p['max_items'],
                    'is_active' => true,
                ]
            );
        }
    }
}