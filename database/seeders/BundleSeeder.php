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
            ['name' => 'Pack de Temporada', 'desc' => 'Productos exclusivos del mes.'],
            ['name' => 'Mega Bundle Omega', 'desc' => 'El pack más completo del catálogo.'],
            ['name' => 'Starter Kit Pro', 'desc' => 'Todo lo necesario para iniciar.'],
        ];

        $startDate = Carbon::now();
        $baseEndDate = Carbon::now()->addMonth();

        foreach ($branches as $branch) {
            foreach ($packDefinitions as $index => $data) {
                // Lógica 50/50: Pares son Editables, Impares son Packs Fijos
                $isEditable = ($index % 2 === 0);
                
                $bundle = Bundle::create([
                    'branch_id'   => $branch->id,
                    'name'        => "{$data['name']} - {$branch->name}",
                    'slug'        => Str::slug($data['name'] . '-' . $branch->name) . '-' . Str::random(4),
                    'description' => $data['desc'],
                    // Si es editable, el precio suele ser dinámico (NULL), si no, es fijo.
                    'fixed_price' => $isEditable ? null : rand(100, 500),
                    'is_editable' => $isEditable,
                    'is_active'   => true,
                    'starts_at'   => $startDate,
                    'ends_at'     => $baseEndDate->copy()->addDays($index),
                ]);

                // Asignar entre 2 y 4 SKUs aleatorios
                $items = [];
                $randomSkus = $skus->random(rand(2, 4));
                foreach ($randomSkus as $sku) {
                    $items[$sku->id] = ['quantity' => rand(1, 3)];
                }

                $bundle->skus()->sync($items);
            }
        }
    }
}