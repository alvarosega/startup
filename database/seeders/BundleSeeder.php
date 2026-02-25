<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Branch;
use App\Models\Sku;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BundleSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $skus = Sku::where('is_active', true)->get();

        if ($branches->isEmpty() || $skus->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            // Creamos 3 packs por sucursal
            $packs = [
                [
                    'name' => "Pack Bienvenida - {$branch->name}",
                    'description' => "Selección premium de la casa para nuevos clientes en {$branch->city}.",
                    'price' => 150.00
                ],
                [
                    'name' => "Combo Parrillero {$branch->name}",
                    'description' => "Todo lo necesario para tu asado de fin de semana.",
                    'price' => null // Precio dinámico (suma de items)
                ],
                [
                    'name' => "Mix de Bebidas {$branch->name}",
                    'description' => "Variedad de hidratantes y energizantes.",
                    'price' => 85.50
                ]
            ];

            foreach ($packs as $packData) {
                $bundle = Bundle::create([
                    'branch_id'   => $branch->id,
                    'name'        => $packData['name'],
                    'slug'        => Str::slug($packData['name']) . '-' . Str::random(4),
                    'description' => $packData['description'],
                    'fixed_price' => $packData['price'],
                    'is_active'   => true,
                ]);

                // Asignar entre 2 y 4 SKUs aleatorios al pack
                $randomSkus = $skus->random(rand(2, 4));
                
                $items = [];
                foreach ($randomSkus as $sku) {
                    $items[$sku->id] = ['quantity' => rand(1, 3)];
                }

                // Sincronización usando el modelo Pivot (sin columna 'id' como corregimos)
                $bundle->skus()->sync($items);
            }
        }
    }
}