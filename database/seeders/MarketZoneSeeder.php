<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketZone;
use App\Models\Category;
use Illuminate\Support\Str;

class MarketZoneSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DEFINICIÓN DE ZONAS ESTRATÉGICAS
        $zones = [
            [
                'name' => 'Licores & Bebidas',
                'slug' => 'licores-bebidas',
                'hex_color' => '#7C3AED',
                'svg_id' => 'zone-liquor',
                'description' => 'Cervezas, vinos, destilados y gaseosas.',
                'keywords' => ['Cerveza', 'Vino', 'Whisky', 'Vodka', 'Refresco', 'Agua', 'Bebida']
            ],
            // ... (Tus otras zonas siguen igual, cópialas aquí) ...
            [
                'name' => 'Almacén & Despensa',
                'slug' => 'almacen-despensa',
                'hex_color' => '#F59E0B',
                'svg_id' => 'zone-pantry',
                'description' => 'Arroz, fideos, aceites y canasta familiar.',
                'keywords' => ['Arroz', 'Fideo', 'Aceite', 'Harina', 'Azucar', 'Conserva', 'Salsa']
            ],
            [
                'name' => 'Frescos & Lácteos',
                'slug' => 'frescos-lacteos',
                'hex_color' => '#10B981',
                'svg_id' => 'zone-fresh',
                'description' => 'Leche, quesos, carnes y verduras.',
                'keywords' => ['Leche', 'Queso', 'Yogur', 'Carne', 'Pollo', 'Huevo', 'Fruta']
            ],
            [
                'name' => 'Hogar & Limpieza',
                'slug' => 'hogar-limpieza',
                'hex_color' => '#3B82F6',
                'svg_id' => 'zone-home',
                'description' => 'Detergentes, cuidado personal y hogar.',
                'keywords' => ['Detergente', 'Jabon', 'Shampoo', 'Papel', 'Limpieza', 'Mascota']
            ]
        ];

        foreach ($zones as $data) {
            $keywords = $data['keywords'];
            unset($data['keywords']);

            // Crear o actualizar la Zona
            $zone = MarketZone::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // CORRECCIÓN CRÍTICA: Convertir ID Hex a Binario para el UPDATE masivo
            // El Accessor del modelo te da Hex, pero la DB quiere Binario.
            // Usamos hex2bin sobre el ID que nos da el modelo.
            $binaryZoneId = hex2bin($zone->id);

            foreach ($keywords as $keyword) {
                Category::where('name', 'LIKE', "%{$keyword}%")
                    ->whereNull('parent_id')
                    ->update(['market_zone_id' => $binaryZoneId]); // Enviamos BINARIO
            }
        }

        // 3. FALLBACK
        $defaultZone = MarketZone::where('slug', 'almacen-despensa')->first();
        
        if ($defaultZone) {
            $binaryDefaultId = hex2bin($defaultZone->id); // Conversión también aquí

            Category::whereNull('market_zone_id')
                ->whereNull('parent_id')
                ->update(['market_zone_id' => $binaryDefaultId]);
        }
    }
}