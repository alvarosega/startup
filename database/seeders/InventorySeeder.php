<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sku;
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use App\Models\Admin; // <--- CAMBIO CLAVE: Usamos Admin
use App\Models\Price;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // ... (validaciones iniciales igual que antes) ...
        if (Sku::count() === 0 || Branch::count() === 0) return;

        $skus = Sku::with('product.brand', 'prices')->get();
        $branches = Branch::all();
        $admin = Admin::first();
        $providers = Provider::all();

        if (!$admin || $providers->isEmpty()) return;

        foreach ($branches as $branch) {
            
            // ... (configuración de estrategia comercial igual) ...
            $config = [ 'type' => 'MIXTO', 'brands' => ['Paceña', 'Coca-Cola'], 'price_factor' => 1.0, 'stock_range' => [10, 30] ];
            if (str_contains($branch->name, 'Sede Central')) {
                 $config = ['type' => 'SUPERMERCADO', 'brands' => ['*'], 'price_factor' => 1.0, 'stock_range' => [20, 50]];
            }

            foreach ($skus as $sku) {
                $brandName = $sku->product->brand->name ?? 'Generico';
                if (!in_array('*', $config['brands']) && !in_array($brandName, $config['brands'])) continue; 

                // A. PRECIOS
                $basePrice = $sku->prices->whereNull('branch_id')->first()?->final_price ?? 10.00;
                $finalPrice = round(($basePrice * $config['price_factor']) * 2) / 2; 

                Price::updateOrCreate(
                    [
                        // CORRECCIÓN: Usar IDs directos (sin hex2bin)
                        'sku_id' => $sku->id, 
                        'branch_id' => $branch->id
                    ],
                    [
                        'list_price' => $finalPrice * 1.2, 
                        'final_price' => $finalPrice,
                        'min_quantity' => 1,
                        'valid_from' => now(),
                    ]
                );

                // B. INVENTARIO
                $qty = rand($config['stock_range'][0], $config['stock_range'][1]);
                $cost = $basePrice * 0.70;

                // 1. Crear Compra
                $purchase = Purchase::create([
                    // CORRECCIÓN: IDs directos
                    'branch_id' => $branch->id,
                    'provider_id' => $sku->product->brand->provider_id ?? $providers->random()->id,
                    'admin_id' => $admin->id,
                    'document_number' => 'INI-' . strtoupper(Str::slug($config['type'])) . '-' . uniqid(),
                    'purchase_date' => now()->subDays(rand(1, 15)),
                    'total_amount' => $qty * $cost,
                    'status' => 'COMPLETED'
                ]);

                // 2. Lote Físico
                $lotCode = sprintf('%s-%s-%s', strtoupper(substr($config['type'], 0, 3)), substr($branch->id, 0, 8), uniqid());

                $lot = InventoryLot::create([
                    // CORRECCIÓN: IDs directos
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'purchase_id' => $purchase->id,
                    'lot_code' => $lotCode,
                    'quantity' => $qty,
                    'initial_quantity' => $qty,
                    'reserved_quantity' => 0,
                    'unit_cost' => $cost,
                    'expiration_date' => now()->addMonths(6),
                ]);

                // 3. Kardex (InventoryMovement)
                InventoryMovement::create([
                    // CORRECCIÓN: IDs directos
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $admin->id,
                    'type' => 'purchase',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'reference' => 'STOCK INICIAL ' . $config['type']
                ]);
            }
        }
    }
}