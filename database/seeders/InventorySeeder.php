<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sku, Branch, Provider, Purchase, InventoryLot, InventoryMovement, Admin};
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        if (Sku::count() === 0 || Branch::count() === 0) {
            $this->command->warn('Faltan SKUs o Sucursales. Abortando sembrado de inventario.');
            return;
        }

        // Obtención de Admin segura. Si no hay con rol, toma el primero activo.
        $admin = Admin::first();
        
        $providers = Provider::all();

        if (!$admin || $providers->isEmpty()) {
            $this->command->warn('Falta Admin o Proveedores. Abortando sembrado.');
            return;
        }

        $skus = Sku::with('product.brand', 'prices')->get();
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $this->command->info("Sembrando inventario: {$branch->name}");
            
            $config = ['brands' => ['Paceña', 'Coca-Cola'], 'stock_range' => [10, 30]];
            if (str_contains(strtolower($branch->name), 'central') || str_contains(strtolower($branch->name), 'sede')) {
                $config = ['brands' => ['*'], 'stock_range' => [50, 100]];
            }

            // Usamos una fecha base para que los ingresos se vean cronológicos en la UI
            $dateCounter = Carbon::now()->subDays(15);

            foreach ($skus as $sku) {
                $brandName = $sku->product->brand->name ?? 'Generico';
                if (!in_array('*', $config['brands']) && !in_array($brandName, $config['brands'])) continue; 

                // Lógica de 10% para RELOT (Stock de Seguridad)
                $isSafety = rand(1, 100) <= 10;
                $docPrefix = $isSafety ? 'EMG' : 'CMP'; // Usamos CMP para mantener coherencia con el formulario
                $lotPrefix = $isSafety ? 'RELOT' : 'LOT';

                $basePrice = $sku->prices->whereNull('branch_id')->first()?->final_price ?? 10.00;
                $qty = rand($config['stock_range'][0], $config['stock_range'][1]);
                $cost = $basePrice * 0.70; // 30% de margen de ganancia simulado
                
                // Avanzamos la fecha para dar sensación de flujo continuo
                $purchaseDate = $dateCounter->addHours(rand(1, 12))->copy();

                // 1. Registro de Compra (Cabecera automatizada)
                $purchase = Purchase::create([
                    'branch_id' => $branch->id,
                    'provider_id' => $sku->product->brand->provider_id ?? $providers->random()->id,
                    'admin_id' => $admin->id,
                    'document_number' => "{$docPrefix}-" . $purchaseDate->format('ymd') . "-" . strtoupper(Str::random(4)),
                    'purchase_date' => $purchaseDate->toDateString(),
                    'total_amount' => $qty * $cost,
                    'status' => 'COMPLETED'
                ]);

                // 2. Lote Físico (Con flag de seguridad y código dinámico)
                $lot = InventoryLot::create([
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'purchase_id' => $purchase->id, // VINCULACIÓN CRÍTICA
                    'lot_code' => "{$lotPrefix}-" . $purchaseDate->format('ymd') . "-" . strtoupper(Str::random(5)),
                    'quantity' => $qty,
                    'initial_quantity' => $qty,
                    'is_safety_stock' => $isSafety,
                    'unit_cost' => $cost,
                    'expiration_date' => now()->addMonths(rand(6, 12)),
                ]);

                // 3. Movimiento de Kardex (Trazabilidad)
                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $admin->id,
                    'type' => 'ENTRY_PURCHASE',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'reference' => "Ingreso Auto #{$purchase->document_number}"
                ]);
            }
        }
        
        $this->command->info("Sembrado de inventario finalizado con éxito.");
    }
}