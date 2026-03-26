<?php

declare(strict_types=1);

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
            $this->command->warn("Seeder abortado: No hay sucursales o SKUs activos.");
            return;
        }

        $definitions = [
            // Tipo: ATOMIC (Combos con precio fijo)
            ['name' => 'Pack Parrillero Pro', 'type' => 'atomic', 'desc' => 'Todo lo necesario para el asado perfecto a un solo precio.'],
            ['name' => 'Combo Gamer Energy', 'type' => 'atomic', 'desc' => 'Snacks y bebidas energéticas para sesiones largas.'],
            
            // Tipo: TEMPLATE (Shopping Templates / Listas de compra)
            ['name' => 'Básicos de Despensa', 'type' => 'template', 'desc' => 'Una selección de esenciales. Elige las cantidades que necesites.'],
            ['name' => 'Kit Limpieza Profunda', 'type' => 'template', 'desc' => 'Plantilla con los mejores productos de aseo para tu hogar.'],
            ['name' => 'Repostería Creativa', 'type' => 'template', 'desc' => 'Harinas, esencias y decoraciones listas para tu carrito.'],
        ];

        foreach ($branches as $branch) {
            foreach ($definitions as $data) {
                $type = $data['type'];
                
                // REGLA DE NEGOCIO: 
                // Atomic = Precio Fijo | Template = Precio Nulo (Suma dinámica de SKUs)
                $fixedPrice = ($type === 'atomic') ? (float) rand(80, 250) : null;

                $bundle = Bundle::create([
                    'branch_id'   => $branch->id,
                    'name'        => $data['name'],
                    'type'        => $type,
                    'slug'        => Str::slug($data['name'] . '-' . $branch->name . '-' . Str::random(4)),
                    'description' => $data['desc'],
                    'fixed_price' => $fixedPrice,
                    'is_active'   => true,
                    'max_quantity_per_order' => 5,
                    'starts_at'   => Carbon::now(),
                    'ends_at'     => Carbon::now()->addMonths(6),
                ]);

                // Asignación de componentes (Receta)
                $items = [];
                // Tomamos entre 3 y 5 SKUs aleatorios para cada bundle
                $selectedSkus = $skus->random(rand(3, 5));
                
                foreach ($selectedSkus as $sku) {
                    $items[$sku->id] = [
                        'quantity' => rand(1, 3) // Cantidad sugerida en la plantilla o fija en el combo
                    ];
                }

                $bundle->skus()->sync($items);
            }
        }

        $this->command->info("BundleSeeder: Se han generado combos y plantillas para " . $branches->count() . " sucursales.");
    }
}