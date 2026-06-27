<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalog\Product;
use App\Models\Catalog\Sku;
use App\Models\Catalog\Brand;
use App\Models\Operations\Provider;
use App\Models\Catalog\Category;
use App\Models\Operations\Branch;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLot;
use App\Models\Users\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    private array $stats = [
        'created' => [],
        'existing' => [],
        'errors' => [],
    ];

    public function run(): void
    {
        $this->command->info('🚀 Iniciando InventorySeeder...');
        $this->command->newLine();

        try {
            DB::transaction(function () {
                // 0. Proveedor
                $this->createProvider();
                
                // 0.1 Categoría
                $this->createCategory();
                
                // 0.2 Marca
                $this->createBrand();
                
                // 1. Admin
                $this->createAdmin();
                
                // 2. Sucursal
                $this->createBranch();
                
                // 3. Producto
                $this->createProduct();
                
                // 4. SKUs
                $this->createSkus();
                
                // 5. Lotes y Movimientos
                $this->createLotsAndMovements();
            });

            // Mostrar resumen final
            $this->showSummary();

        } catch (\Exception $e) {
            $this->command->error('❌ Error crítico: ' . $e->getMessage());
            $this->stats['errors'][] = [
                'entity' => 'TRANSACTION',
                'error' => $e->getMessage()
            ];
            $this->showSummary();
            throw $e;
        }
    }

    private function createProvider(): object
    {
        $this->command->line('📦 Procesando Proveedor...');
        
        try {
            $provider = Provider::firstOrCreate(
                ['company_name' => 'PROVEEDOR GENÉRICO'],
                [
                    'id' => (string) Str::uuid(),
                    'commercial_name' => 'PROVEEDOR GENÉRICO',
                    'slug' => Str::slug('PROVEEDOR GENÉRICO'),
                    'tax_id' => '0',
                    'internal_code' => 'PROV-GEN-001',
                    'contact_name' => 'Contacto Genérico',
                    'email_orders' => 'proveedor@generico.com',
                    'phone' => '59100000000',
                    'address' => 'Dirección Genérica',
                    'city' => 'La Paz',
                    'lead_time_days' => 7,
                    'min_order_value' => 0,
                    'credit_days' => 0,
                    'credit_limit' => 0,
                    'is_active' => true,
                    'deleted_epoch' => 0,
                ]
            );

            $this->logEntity('Proveedor', $provider->company_name, $provider->wasRecentlyCreated);
            return $provider;

        } catch (\Exception $e) {
            $this->logError('Proveedor', $e->getMessage());
            throw $e;
        }
    }

    private function createCategory(): object
    {
        $this->command->line('📁 Procesando Categoría...');
        
        try {
            $category = Category::firstOrCreate(
                ['name' => 'CATEGORÍA GENÉRICA'],
                [
                    'id' => (string) Str::uuid(),
                    'parent_id' => null,
                    'slug' => Str::slug('CATEGORÍA GENÉRICA'),
                    'external_code' => 'CAT-GEN-001',
                    'tax_classification' => 'general',
                    'requires_age_check' => false,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => 0,
                    'deleted_epoch' => 0,
                ]
            );

            $this->logEntity('Categoría', $category->name, $category->wasRecentlyCreated);
            return $category;

        } catch (\Exception $e) {
            $this->logError('Categoría', $e->getMessage());
            throw $e;
        }
    }

    private function createBrand(): object
    {
        $this->command->line('🏷️  Procesando Marca...');
        
        $provider = Provider::where('company_name', 'PROVEEDOR GENÉRICO')->first();
        $category = Category::where('name', 'CATEGORÍA GENÉRICA')->first();
        
        try {
            $brand = Brand::firstOrCreate(
                ['name' => 'MARCA GENÉRICA'],
                [
                    'id' => (string) Str::uuid(),
                    'parent_id' => null,
                    'provider_id' => $provider->id,
                    'category_id' => $category->id,
                    'slug' => Str::slug('MARCA GENÉRICA'),
                    'deleted_epoch' => 0,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => 0,
                ]
            );

            $this->logEntity('Marca', $brand->name, $brand->wasRecentlyCreated);
            return $brand;

        } catch (\Exception $e) {
            $this->logError('Marca', $e->getMessage());
            throw $e;
        }
    }

    private function createAdmin(): object
    {
        $this->command->line('👤 Procesando Administrador...');
        
        try {
            $adminData = [
                'first_name' => 'Gerente',
                'last_name' => 'General',
                'phone' => '59178710820',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ];

            if (DB::getSchemaBuilder()->hasColumn('admins', 'role_name')) {
                $adminData['role_name'] = 'super_admin';
            }

            $admin = Admin::firstOrCreate(
                ['email' => 'admin@admin.com'],
                array_merge(['id' => (string) Str::uuid()], $adminData)
            );

            if (!isset($adminData['role_name']) && method_exists($admin, 'assignRole')) {
                if (!$admin->hasRole('super_admin')) {
                    $admin->assignRole('super_admin');
                    $this->command->line('   ↳ Rol "super_admin" asignado correctamente');
                }
            }

            $this->logEntity('Administrador', $admin->email, $admin->wasRecentlyCreated);
            return $admin;

        } catch (\Exception $e) {
            $this->logError('Administrador', $e->getMessage());
            throw $e;
        }
    }

    private function createBranch(): object
    {
        $this->command->line('🏢 Procesando Sucursal...');
        
        try {
            $branch = Branch::firstOrCreate(
                ['deleted_epoch' => 0],
                [
                    'id' => (string) Str::uuid(),
                    'name' => 'SUCURSAL CENTRAL NORTE',
                    'deleted_epoch' => 0
                ]
            );

            $this->logEntity('Sucursal', $branch->name, $branch->wasRecentlyCreated);
            return $branch;

        } catch (\Exception $e) {
            $this->logError('Sucursal', $e->getMessage());
            throw $e;
        }
    }

    private function createProduct(): object
    {
        $this->command->line('🍾 Procesando Producto...');
        
        $brand = Brand::where('name', 'MARCA GENÉRICA')->first();
        $category = Category::where('name', 'CATEGORÍA GENÉRICA')->first();
        
        try {
            $product = Product::firstOrCreate(
                ['name' => 'LÍNEA DE BEBIDAS Y DESTILADOS CORE'],
                [
                    'id' => (string) Str::uuid(),
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'slug' => Str::slug('LÍNEA DE BEBIDAS Y DESTILADOS CORE'),
                    'deleted_epoch' => 0,
                    'is_active' => true,
                    'is_featured' => false,
                    'is_alcoholic' => true,
                    'sort_order' => 0,
                ]
            );

            $this->logEntity('Producto', $product->name, $product->wasRecentlyCreated);
            return $product;

        } catch (\Exception $e) {
            $this->logError('Producto', $e->getMessage());
            throw $e;
        }
    }

    private function createSkus(): object
    {
        $this->command->line('📊 Procesando SKUs...');
        
        $product = Product::where('name', 'LÍNEA DE BEBIDAS Y DESTILADOS CORE')->first();
        
        $skus = Sku::limit(3)->get();
        $createdCount = 0;
        $existingCount = 0;
        
        if ($skus->isEmpty()) {
            $skuData = [
                ['code' => 'GIN-GOR-750', 'name' => 'GIN GORDONS 750ML'],
                ['code' => 'TON-EVE-354', 'name' => 'TONICA EVERVESS 354ML'],
                ['code' => 'CER-PAC-473', 'name' => 'CERVEZA PACEÑA LATA 473ML'],
            ];

            $skus = collect();
            foreach ($skuData as $data) {
                try {
                    $sku = Sku::firstOrCreate(
                        ['code' => $data['code']],
                        [
                            'id' => (string) Str::uuid(),
                            'product_id' => $product->id,
                            'name' => $data['name'],
                            'code' => $data['code']
                        ]
                    );
                    
                    if ($sku->wasRecentlyCreated) {
                        $createdCount++;
                    } else {
                        $existingCount++;
                    }
                    
                    $skus->push($sku);
                    $this->command->line('   ' . ($sku->wasRecentlyCreated ? '✅' : 'ℹ️') . " SKU: {$data['name']} ({$data['code']})");
                    
                } catch (\Exception $e) {
                    $this->logError("SKU: {$data['name']}", $e->getMessage());
                }
            }
        }

        $this->stats['created']['SKUs'] = $createdCount;
        $this->stats['existing']['SKUs'] = $existingCount;

        return $skus;
    }

    private function createLotsAndMovements(): void
    {
        $branch = Branch::where('deleted_epoch', 0)->first();
        $admin = Admin::where('email', 'admin@admin.com')->first();
        $skus = Sku::limit(3)->get();
        
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $branch->name), 0, 4));
        
        $lotsCreated = 0;
        $lotsExisting = 0;
        $movementsCreated = 0;
        
        $this->command->line('📦 Procesando Lotes y Movimientos...');

        foreach ($skus as $index => $sku) {
            $this->command->line('   🔄 SKU: ' . $sku->name);
            
            try {
                // Lote 1
                $lot1Code = "LOT-{$prefix}-2026-00001";
                $lot1 = InventoryLot::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'lot_code' => $lot1Code,
                        'deleted_epoch' => 0
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'purchase_id' => null,
                        'transfer_id' => null,
                        'sku_id' => $sku->id,
                        'quantity' => 150.000,
                        'initial_quantity' => 150.000,
                        'safety_quantity' => 0.000,
                        'initial_safety_quantity' => 0.000,
                        'reserved_quantity' => 0.000,
                        'cost_price' => 12.50,
                        'is_quarantine' => false,
                        'expiration_date' => now()->addMonths(12)->toDateString(),
                        'deleted_epoch' => 0,
                    ]
                );

                if ($lot1->wasRecentlyCreated) {
                    $lotsCreated++;
                    // Movimiento Lote 1
                    DB::table('inventory_movements')->insert([
                        'id' => (string) Str::uuid(),
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'inventory_lot_id' => $lot1->id,
                        'admin_id' => $admin->id,
                        'type' => 'IN_PURCHASE',
                        'quantity' => 150.000,
                        'balance_after' => 150.000,
                        'reference' => 'Carga Inicial Seeder',
                        'reason' => 'Población automatizada del entorno de desarrollo',
                        'created_at' => now(),
                    ]);
                    $movementsCreated++;
                    $this->command->line('      ✅ Lote 1 creado: ' . $lot1Code);
                } else {
                    $lotsExisting++;
                    $this->command->line('      ℹ️  Lote 1 existente: ' . $lot1Code);
                }

                // Lote 2
                $lot2Code = "LOT-{$prefix}-2026-00002";
                $lot2 = InventoryLot::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'lot_code' => $lot2Code,
                        'deleted_epoch' => 0
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'purchase_id' => null,
                        'transfer_id' => null,
                        'sku_id' => $sku->id,
                        'quantity' => 30.000,
                        'initial_quantity' => 50.000,
                        'safety_quantity' => 20.000,
                        'initial_safety_quantity' => 20.000,
                        'reserved_quantity' => 0.000,
                        'cost_price' => 14.10,
                        'is_quarantine' => false,
                        'expiration_date' => now()->addMonths(18)->toDateString(),
                        'deleted_epoch' => 0,
                    ]
                );

                if ($lot2->wasRecentlyCreated) {
                    $lotsCreated++;
                    // Movimientos Lote 2
                    DB::table('inventory_movements')->insert([
                        'id' => (string) Str::uuid(),
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'inventory_lot_id' => $lot2->id,
                        'admin_id' => $admin->id,
                        'type' => 'IN_PURCHASE',
                        'quantity' => 50.000,
                        'balance_after' => 50.000,
                        'reference' => 'Carga Inicial Seeder',
                        'reason' => 'Población automatizada del entorno de desarrollo',
                        'created_at' => now(),
                    ]);

                    DB::table('inventory_movements')->insert([
                        'id' => (string) Str::uuid(),
                        'branch_id' => $branch->id,
                        'sku_id' => $sku->id,
                        'inventory_lot_id' => $lot2->id,
                        'admin_id' => $admin->id,
                        'type' => 'BLOCK_SAFETY',
                        'quantity' => 20.000,
                        'balance_after' => 30.000,
                        'reference' => 'Reserva de Seguridad Inicial',
                        'reason' => 'Segmentación automática de stock de colchón',
                        'created_at' => now(),
                    ]);
                    $movementsCreated += 2;
                    $this->command->line('      ✅ Lote 2 creado: ' . $lot2Code . ' (2 movimientos)');
                } else {
                    $lotsExisting++;
                    $this->command->line('      ℹ️  Lote 2 existente: ' . $lot2Code);
                }

                // Balance
                InventoryBalance::updateOrCreate(
                    [
                        'sku_id'    => $sku->id,
                        'branch_id' => $branch->id
                    ],
                    [
                        'total_physical'   => 200.000,
                        'total_reserved'   => 0.000,
                        'total_safety'     => 20.000,
                        'total_quarantine' => 0.000,
                        'updated_at'       => now()
                    ]
                );
                $this->command->line('      📊 Balance actualizado');

            } catch (\Exception $e) {
                $this->logError("Lotes SKU: {$sku->name}", $e->getMessage());
            }
        }

        $this->stats['created']['Lotes'] = $lotsCreated;
        $this->stats['existing']['Lotes'] = $lotsExisting;
        $this->stats['created']['Movimientos'] = $movementsCreated;
    }

    private function logEntity(string $entity, string $name, bool $wasCreated): void
    {
        if ($wasCreated) {
            $this->command->line("   ✅ {$entity} creado: {$name}");
            $this->stats['created'][$entity] = ($this->stats['created'][$entity] ?? 0) + 1;
        } else {
            $this->command->line("   ℹ️  {$entity} ya existente: {$name}");
            $this->stats['existing'][$entity] = ($this->stats['existing'][$entity] ?? 0) + 1;
        }
    }

    private function logError(string $entity, string $error): void
    {
        $this->command->error("   ❌ Error en {$entity}: {$error}");
        $this->stats['errors'][] = [
            'entity' => $entity,
            'error' => $error
        ];
    }

    private function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('📊 RESUMEN DE EJECUCIÓN');
        $this->command->info('═══════════════════════════════════════');
        
        // Creados
        if (!empty($this->stats['created'])) {
            $this->command->newLine();
            $this->command->line('✅ REGISTROS CREADOS:');
            $totalCreated = 0;
            foreach ($this->stats['created'] as $entity => $count) {
                if ($count > 0) {
                    $this->command->line("   • {$entity}: {$count}");
                    $totalCreated += $count;
                }
            }
            $this->command->line("   ────────────────────");
            $this->command->line("   TOTAL CREADOS: {$totalCreated}");
        }

        // Existentes
        if (!empty($this->stats['existing'])) {
            $this->command->newLine();
            $this->command->line('ℹ️  REGISTROS YA EXISTENTES:');
            $totalExisting = 0;
            foreach ($this->stats['existing'] as $entity => $count) {
                if ($count > 0) {
                    $this->command->line("   • {$entity}: {$count}");
                    $totalExisting += $count;
                }
            }
            $this->command->line("   ────────────────────");
            $this->command->line("   TOTAL EXISTENTES: {$totalExisting}");
        }

        // Errores
        if (!empty($this->stats['errors'])) {
            $this->command->newLine();
            $this->command->error('❌ ERRORES ENCONTRADOS:');
            foreach ($this->stats['errors'] as $error) {
                $this->command->line("   • {$error['entity']}: {$error['error']}");
            }
            $this->command->line("   TOTAL ERRORES: " . count($this->stats['errors']));
        }

        // Resumen general
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        
        if (empty($this->stats['errors'])) {
            $this->command->info('✅ InventorySeeder ejecutado exitosamente');
        } else {
            $this->command->warn('⚠️  InventorySeeder completado con errores');
        }
        
        $this->command->info('═══════════════════════════════════════');
        $this->command->newLine();
    }
}