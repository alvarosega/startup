<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sku;
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\InventoryLot;      // Antes LoteInventario
use App\Models\InventoryMovement; // Antes MovimientoInventario
use App\Models\User;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $skus = Sku::with('product.brand')->get(); 
        $branches = Branch::all();
        $allProviders = Provider::all();
        $user = User::first(); 

        if ($skus->isEmpty() || $branches->isEmpty()) return;

        foreach ($branches as $branch) {
            foreach ($skus as $sku) {
                if (rand(0, 100) > 70) { 
                    
                    // 1. Determinar Proveedor
                    $officialProviderId = $sku->product?->brand?->provider_id;
                    $providerId = ($officialProviderId && rand(0, 100) < 90) 
                        ? $officialProviderId 
                        : $allProviders->random()->id;

                    $cantidad = rand(10, 200);
                    // Obtenemos precio actual
                    // Usamos ?-> para que si getPriceForBranch devuelve null, no explote.
                    $currentPrice = $sku->getPriceForBranch(null)?->final_price ?? 10;          
                    $costo = $currentPrice * 0.70;

                    // 2. CREAR COMPRA (Purchase)
                    $purchase = Purchase::create([
                        'branch_id' => $branch->id,
                        'provider_id' => $providerId,
                        'user_id' => $user->id ?? 1,
                        'document_number' => 'FACT-' . rand(10000, 99999),
                        'purchase_date' => now()->subDays(rand(1, 30)),
                        'total_amount' => $cantidad * $costo,
                        'status' => 'COMPLETED'
                    ]);

                    // 3. CREAR LOTE (InventoryLot)
                    $lot = InventoryLot::create([
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'purchase_id' => $purchase->id,
                        'lot_code' => 'LOT-' . now()->format('y') . Str::upper(Str::random(4)),
                        
                        'quantity' => $cantidad,         // Saldo actual
                        'initial_quantity' => $cantidad, // <--- FALTABA ESTA LÍNEA (Igual a la cantidad comprada)
                        'reserved_quantity' => 0,
                        
                        'unit_cost' => $costo,
                        'expiration_date' => now()->addMonths(rand(3, 24)),
                    ]);

                    // 4. CREAR MOVIMIENTO (InventoryMovement)
                    InventoryMovement::create([
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'inventory_lot_id' => $lot->id,
                        'user_id' => $user->id ?? 1,
                        'type' => 'purchase',
                        'quantity' => $cantidad,
                        'unit_cost' => $costo,
                        'reference' => 'PURCHASE #' . $purchase->document_number
                    ]);
                }
            }
        }
    }
}