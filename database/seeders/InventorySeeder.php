<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sku, Branch, Provider, Purchase, InventoryLot, InventoryMovement, Admin};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::first() ?? abort(1, 'No hay Admin para sembrar.');
        $providers = Provider::all();
        $skus = Sku::with('product.brand', 'prices')->get();
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $this->command->info("Silo: {$branch->name} - Iniciando transferencia de datos...");

            foreach ($skus as $sku) {
                // ... (Lógica de filtrado de marcas se mantiene igual)
                
                $isSafety = rand(1, 100) <= 15;
                $qty = rand(20, 100);
                $cost = ($sku->prices->whereNull('branch_id')->first()?->final_price ?? 10) * 0.7;

                DB::transaction(function () use ($branch, $sku, $admin, $providers, $isSafety, $qty, $cost) {
                    // 1. Cabecera
                    $purchase = Purchase::create([
                        'branch_id' => $branch->id,
                        'provider_id' => $sku->product->brand->provider_id ?? $providers->random()->id,
                        'admin_id' => $admin->id,
                        'document_number' => ($isSafety ? 'EMG-' : 'CMP-') . strtoupper(Str::random(8)),
                        'purchase_date' => now()->subDays(rand(0, 10)),
                        'total_amount' => $qty * $cost,
                        'status' => 'COMPLETED'
                    ]);

                    // 2. Lote
                    $lot = InventoryLot::create([
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'purchase_id' => $purchase->id,
                        'lot_code' => ($isSafety ? 'RELOT-' : 'LOT-') . strtoupper(Str::random(6)),
                        'quantity' => $qty,
                        'initial_quantity' => $qty,
                        'is_safety_stock' => $isSafety,
                        'unit_cost' => $cost,
                    ]);

                    // 3. Kardex (Inmutable)
                    InventoryMovement::create([
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'inventory_lot_id' => $lot->id,
                        'admin_id' => $admin->id,
                        'type' => 'ENTRY_PURCHASE',
                        'quantity' => $qty,
                        'unit_cost' => $cost,
                        'reference' => "Seed: {$purchase->document_number}"
                    ]);

                    DB::table('inventory_balances')->updateOrInsert(
                        ['branch_id' => $branch->id, 'sku_id' => $sku->id],
                        [
                            // Usamos incrementos atómicos para evitar colisiones de datos
                            'total_physical' => DB::raw("total_physical + {$qty}"),
                            'total_reserved' => DB::raw("total_reserved"), // Se inicializa en 0 por default en migración
                            'total_safety'   => $isSafety ? DB::raw("total_safety + {$qty}") : DB::raw("total_safety"),
                            'updated_at'     => now(),
                            'created_at'     => now()
                        ]
                    );
                });
            }
        }
    }
}