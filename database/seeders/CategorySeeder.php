<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cervezas',
                'adult' => true,
                'subs' => ['Lager Industrial', 'Cerveza de Trigo', 'Cerveza Negra / Stout', 'Cerveza Artesanal IPA', 'Cerveza Importada']
            ],
            [
                'name' => 'Destilados',
                'adult' => true,
                'subs' => ['Singani', 'Whisky Escocés', 'Ron Añejo', 'Vodka Premium', 'Gin & Botánicos']
            ],
            [
                'name' => 'Vinos',
                'adult' => true,
                'subs' => ['Vino Tinto Varietal', 'Vino Blanco', 'Vino Rosado', 'Vino Espumoso', 'Vino de Postre']
            ],
            [
                'name' => 'Gaseosas',
                'adult' => false,
                'subs' => ['Colas Negras', 'Sabores Frutales', 'Gaseosa Zero Azúcar', 'Ginger Ale & Tónicas', 'Sodas Saborizadas']
            ],
            [
                'name' => 'Aguas & Hidratantes',
                'adult' => false,
                'subs' => ['Agua Sin Gas', 'Agua Con Gas', 'Bebidas Isotónicas', 'Agua Saborizada', 'Agua Tónica Premium']
            ],
            [
                'name' => 'Energizantes',
                'adult' => false,
                'subs' => ['Energizante Regular', 'Energizante Sin Azúcar', 'Bebidas de Café', 'Shots Energéticos', 'Energizante Natural']
            ],
            [
                'name' => 'Jugos & Néctares',
                'adult' => false,
                'subs' => ['Jugo de Naranja', 'Néctar de Durazno', 'Jugo de Manzana', 'Multifruta', 'Jugos Detox']
            ],
            [
                'name' => 'Licores & Cremas',
                'adult' => true,
                'subs' => ['Crema de Leche/Café', 'Licores de Hierbas', 'Vermouth', 'Triple Sec & Mixers', 'Aperitivos']
            ],
            [
                'name' => 'Snacks Salados',
                'adult' => false,
                'subs' => ['Papas Fritas', 'Nachos & Tortillas', 'Frutos Secos', 'Galletas Saladas', 'Palitos & Pretzels']
            ],
            [
                'name' => 'Cigarros & Tabaco',
                'adult' => true,
                'subs' => ['Cigarros Rubios', 'Cigarros Mentolados', 'Tabaco para Armar', 'Puros', 'Accesorios de Fumar']
            ]
        ];

        foreach ($categories as $catData) {
            $parent = Category::firstOrCreate([
                'name' => $catData['name'],
                'slug' => Str::slug($catData['name']),
            ], [
                'is_active' => true,
                'requires_age_check' => $catData['adult']
            ]);

            // EXTRACCIÓN QUIRÚRGICA: Obtenemos el binario puro (16 bytes)
            // No usamos $parent->id porque el Trait lo convertiría a Hex (32 caracteres)
            $parentId = $parent->id;

            foreach ($catData['subs'] as $subName) {
                Category::firstOrCreate([
                    'name' => $subName,
                    'slug' => Str::slug($subName),
                    'parent_id' => $parentId
                ], [
                    'is_active' => true,
                    'requires_age_check' => $catData['adult']
                ]);
            }
        }
    }
}