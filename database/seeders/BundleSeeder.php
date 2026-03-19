<?php

namespace Database\Seeders;

use App\Models\{Bundle, Branch, Sku};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BundleSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $skus = Sku::where('is_active', true)->get();

        if ($branches->isEmpty() || $skus->isEmpty()) return;

        $packDefinitions = [
            ['name' => 'Flash Sale Alpha', 'desc' => 'Oferta de tiempo limitado nivel 1.'],
            ['name' => 'Flash Sale Beta', 'desc' => 'Oferta de tiempo limitado nivel 2.'],
            ['name' => 'Pack Nocturno', 'desc' => 'Solo para compras de madrugada.'],
            ['name' => 'Combo Gamer', 'desc' => 'Energía y snacks para maratones.'],
            ['name' => 'Kit de Supervivencia', 'desc' => 'Básicos esenciales de la sucursal.'],
        ];

        foreach ($branches as $branch) {
            foreach ($packDefinitions as $index => $data) {
                $isEditable = ($index % 2 === 0);
                
                $bundle = Bundle::create([
                    'branch_id'   => $branch->id,
                    'name'        => "{$data['name']} - {$branch->name}",
                    'slug'        => Str::slug($data['name'] . '-' . $branch->name) . '-' . Str::random(4),
                    'description' => $data['desc'],
                    'fixed_price' => $isEditable ? null : rand(100, 500),
                    'is_editable' => $isEditable,
                    'max_quantity_per_order' => 5, // Límite de seguridad
                    'is_active'   => true,
                    'starts_at'   => Carbon::now(),
                    'ends_at'     => Carbon::now()->addMonths(2),
                ]);

                // Asignar componentes a la receta del bundle
                $items = [];
                $randomSkus = $skus->random(rand(2, 3));
                foreach ($randomSkus as $sku) {
                    $items[$sku->id] = ['quantity' => rand(1, 2)];
                }

                $bundle->skus()->sync($items);
            }
        }
    }
}