<?php

namespace Database\Seeders;

use App\Models\AdPlacement;
use Illuminate\Database\Seeder;

class AdPlacementSeeder extends Seeder
{
    public function run(): void
    {
        $placements = [
            ['name' => 'Home Hero Main', 'code' => 'HOME_HERO', 'max_items' => 5],
            ['name' => 'Pasillo Categoría', 'code' => 'CATEGORY_HERO', 'max_items' => 1], // FALTABA ESTE
            ['name' => 'Cabecera de Packs', 'code' => 'BUNDLE_HERO', 'max_items' => 1],   // FALTABA ESTE
            ['name' => 'Resultados Búsqueda', 'code' => 'SEARCH_TOP', 'max_items' => 2],
        ];
    
        foreach ($placements as $p) {
            AdPlacement::updateOrCreate(['code' => $p['code']], [
                'name' => $p['name'],
                'max_items' => $p['max_items'],
                'is_active' => true,
            ]);
        }
    }
}