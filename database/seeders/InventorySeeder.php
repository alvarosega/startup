<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sku, Branch, Provider, Purchase, InventoryLot, InventoryMovement, Admin, Price};
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        if (Sku::count() === 0 || Branch::count() === 0) return;

        $skus = Sku::with('product.brand', 'prices')->get();
        $branches = Branch::all();
        $admin = Admin::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first();
        $providers = Provider::all();

        if (!$admin || $providers->isEmpty()) return;

        foreach ($branches as $branch) {
            $this->command->info("Sembrando inventario: {$branch->name}");
            
            $config = ['brands' => ['Paceña', 'Coca-Cola'], 'stock_range' => [10, 30]];
            if (str_contains($branch->name, 'Sede Central')) {
                $config = ['brands' => ['*'], 'stock_range' => [50, 100]];
            }

            foreach ($skus as $sku) {
                $brandName = $sku->product->brand->name ?? 'Generico';
                if (!in_array('*', $config['brands']) && !in_array($brandName, $config['brands'])) continue; 

                $basePrice = $sku->prices->whereNull('branch_id')->first()?->final_price ?? 10.00;
                $qty = rand($config['stock_range'][0], $config['stock_range'][1]);
                $cost = $basePrice * 0.70;

                // 1. Registro de Compra
                $purchase = Purchase::create([
                    'branch_id' => $branch->id,
                    'provider_id' => $sku->product->brand->provider_id ?? $providers->random()->id,
                    'admin_id' => $admin->id,
                    'document_number' => 'INI-' . strtoupper(Str::random(5)),
                    'purchase_date' => now()->subDays(rand(1, 15)),
                    'total_amount' => $qty * $cost,
                    'status' => 'COMPLETED'
                ]);

                // 2. Lote Físico
                $lot = InventoryLot::create([
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'purchase_id' => $purchase->id,
                    'lot_code' => 'LOT-' . strtoupper(Str::random(8)),
                    'quantity' => $qty,
                    'initial_quantity' => $qty,
                    'unit_cost' => $cost,
                    'expiration_date' => now()->addMonths(rand(6, 12)),
                ]);

                // 3. Kardex
                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'inventory_lot_id' => $lot->id,
                    'admin_id' => $admin->id,
                    'type' => 'ENTRY_PURCHASE',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'reference' => 'STOCK INICIAL'
                ]);
            }
        }
    }
}