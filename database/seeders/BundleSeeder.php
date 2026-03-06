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

        if ($branches->isEmpty() || $skus->isEmpty()) {
            return;
        }

        // Definición de los 7 tipos de bundles solicitados
        $packDefinitions = [
            ['name' => 'Flash Sale Alpha', 'desc' => 'Oferta de tiempo limitado nivel 1.'],
            ['name' => 'Flash Sale Beta', 'desc' => 'Oferta de tiempo limitado nivel 2.'],
            ['name' => 'Pack Nocturno', 'desc' => 'Solo para compras de madrugada.'],
            ['name' => 'Combo Gamer', 'desc' => 'Energía y snacks para maratones.'],
            ['name' => 'Kit de Supervivencia', 'desc' => 'Básicos esenciales de la sucursal.'],
            ['name' => 'Pack de Temporada', 'desc' => 'Productos exclusivos del mes.'],
            ['name' => 'Mega Bundle Omega', 'desc' => 'El pack más completo del catálogo.'],
        ];

        $startDate = Carbon::create(2026, 3, 3, 0, 0, 0);
        $baseEndDate = Carbon::create(2026, 4, 3, 23, 59, 59);

        foreach ($branches as $branch) {
            foreach ($packDefinitions as $index => $data) {
                // Cálculo escalonado de fecha de vencimiento (Día 3, 4, 5...)
                $endsAt = $baseEndDate->copy()->addDays($index);

                $bundle = Bundle::create([
                    'branch_id'     => $branch->id,
                    'name'          => "{$data['name']} - {$branch->name}",
                    'slug'          => Str::slug($data['name'] . '-' . $branch->name) . '-' . Str::random(4),
                    'description'   => $data['desc'],
                    'fixed_price'   => rand(50, 500),
                    'is_active'     => true,
                    'starts_at'     => $startDate,
                    'ends_at'       => $endsAt,
                ]);

                // Asignación de Items (Pivote sin ID)
                $randomSkus = $skus->random(rand(2, 4));
                $items = [];
                foreach ($randomSkus as $sku) {
                    $items[$sku->id] = ['quantity' => rand(1, 5)];
                }

                $bundle->skus()->sync($items);
            }
        }
    }
}