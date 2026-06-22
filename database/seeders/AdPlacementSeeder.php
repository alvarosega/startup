<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RetailMedia\AdPlacement;
use Illuminate\Support\Str;

class AdPlacementSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            ['name' => 'BRAND BANNER HERO', 'code' => 'BRAND_HERO', 'max_items' => 5],
            ['name' => 'BRAND CATEGORY TOP', 'code' => 'CATEGORY_HERO', 'max_items' => 1],
            ['name' => 'BRAND BUNDLES GRID', 'code' => 'BUNDLE_HERO', 'max_items' => 1],
            ['name' => 'RESULTADOS BÚSQUEDA PATROCINADOS', 'code' => 'SEARCH_TOP', 'max_items' => 2],
        ];

        foreach ($placements as $p) {
            AdPlacement::updateOrCreate(
                ['code' => $p['code']],
                [
                    'id'        => (string) Str::uuid(),
                    'name'      => $p['name'],
                    'max_items' => $p['max_items'],
                    'is_active' => true,
                ]
            );
        }
    }
}