<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sku;
use App\Models\Branch;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\InventoryLot;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Models\Price;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Cargamos todos los SKUs con su marca para poder filtrar
        $skus = Sku::with('product.brand', 'prices')->get();
        $branches = Branch::all();
        $allProviders = Provider::all();
        $user = User::first(); 

        if ($skus->isEmpty() || $branches->isEmpty()) return;

        foreach ($branches as $branch) {
            
            // =================================================================
            // 1. ESTRATEGIA COMERCIAL POR SUCURSAL (EXCLUSIVIDAD)
            // =================================================================
            
            // Definimos configuración por defecto (Fallback)
            $config = [
                'type' => 'MIXTO',
                'brands' => ['Paceña', 'Coca-Cola'], 
                'price_factor' => 1.0,
                'stock_range' => [10, 30]
            ];

            // APLICAMOS LÓGICA SEGÚN EL NOMBRE O CIUDAD DE LA SUCURSAL
            if (str_contains($branch->name, 'Sopocachi') || str_contains($branch->name, 'Sede Central')) {
                $config = [
                    'type' => 'SUPERMERCADO',
                    'brands' => ['*'], 
                    'price_factor' => 1.0, 
                    'stock_range' => [20, 50]
                ];
            } 
            elseif (str_contains($branch->name, 'Satélite') || str_contains($branch->name, 'El Alto')) {
                $config = [
                    'type' => 'AGENCIA_CBN',
                    'brands' => ['Paceña', 'Huari'], 
                    'price_factor' => 0.92, 
                    'stock_range' => [200, 500] 
                ];
            } 
            elseif (str_contains($branch->name, 'Equipetrol') || str_contains($branch->name, 'Santa Cruz')) {
                $config = [
                    'type' => 'AGENCIA_EMBOL',
                    'brands' => ['Coca-Cola'], 
                    'price_factor' => 1.05, 
                    'stock_range' => [100, 300]
                ];
            } 
            elseif (str_contains($branch->name, 'Calacoto') || str_contains($branch->name, 'Sur')) {
                $config = [
                    'type' => 'BOUTIQUE_LICORES',
                    'brands' => ['Johnnie Walker', 'Fernet Branca', 'Casa Real', 'Corona'], 
                    'price_factor' => 1.25, 
                    'stock_range' => [5, 15] 
                ];
            } 
            elseif (str_contains($branch->city, 'Cochabamba')) {
                $config = [
                    'type' => 'LICORERIA_POPULAR',
                    'brands' => ['Fernet Branca', 'Coca-Cola', 'Paceña'], 
                    'price_factor' => 1.0,
                    'stock_range' => [30, 60]
                ];
            }

            // =================================================================
            // 2. PROCESAMIENTO DE STOCK
            // =================================================================

            foreach ($skus as $sku) {
                $brandName = $sku->product->brand->name;

                // FILTRO ESTRICTO: Si la marca no está en la lista y no es '*', saltamos
                if (!in_array('*', $config['brands']) && !in_array($brandName, $config['brands'])) {
                    continue; 
                }

                // --- A. CÁLCULO DE PRECIOS ---
                $basePrice = $sku->prices->whereNull('branch_id')->first()?->final_price ?? 10.00;
                $finalPrice = round(($basePrice * $config['price_factor']) * 2) / 2; 

                Price::updateOrCreate(
                    ['sku_id' => $sku->id, 'branch_id' => $branch->id],
                    [
                        'list_price' => $finalPrice * 1.2, 
                        'final_price' => $finalPrice,
                        'min_quantity' => 1,
                        'valid_from' => now(),
                    ]
                );

                // --- B. CREACIÓN DE INVENTARIO ---
                
                $qty = rand($config['stock_range'][0], $config['stock_range'][1]);
                $cost = $basePrice * 0.70;

                // 1. Compra 
                $purchase = Purchase::create([
                    'branch_id' => $branch->id,
                    'provider_id' => $sku->product->brand->provider_id ?? $allProviders->random()->id,
                    'user_id' => $user->id ?? 1,
                    // Usamos uniqid para evitar duplicados en numero de documento masivo
                    'document_number' => 'INI-' . strtoupper(Str::slug($config['type'])) . '-' . uniqid(),
                    'purchase_date' => now()->subDays(rand(1, 15)),
                    'total_amount' => $qty * $cost,
                    'status' => 'COMPLETED'
                ]);

                // 2. Lote Físico
                // CORRECCION AQUI: Añadimos branch_id y usamos uniqid() para garantizar unicidad total
                $lotCode = sprintf(
                    '%s-%s-%s',
                    strtoupper(substr($config['type'], 0, 3)), // Ej: LIC
                    $branch->id,                                 // Ej: 15 (Diferencia sucursales)
                    uniqid()                                     // Ej: 65b8e... (Aleatorio único basado en tiempo)
                );

                $lot = InventoryLot::create([
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'purchase_id' => $purchase->id,
                    'lot_code' => $lotCode,
                    'quantity' => $qty,
                    'initial_quantity' => $qty,
                    'reserved_quantity' => 0,
                    'unit_cost' => $cost,
                    'expiration_date' => $sku->product->is_alcoholic ? null : now()->addMonths(6),
                ]);

                // 3. Kardex
                InventoryMovement::create([
                    'branch_id' => $branch->id,
                    'sku_id' => $sku->id,
                    'inventory_lot_id' => $lot->id,
                    'user_id' => $user->id ?? 1,
                    'type' => 'purchase',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'reference' => 'STOCK INICIAL ' . $config['type']
                ]);
            }
        }
    }
}