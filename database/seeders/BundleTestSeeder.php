<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BundleTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Extracción de dependencias del catálogo
        $skuIds = DB::table('skus')->where('is_active', true)->pluck('id')->toArray();

        if (count($skuIds) < 5) {
            throw new \RuntimeException('Error: Se requieren al menos 5 SKUs activos en la base de datos para estructurar los combos de prueba.');
        }

        // Definición explícita del set de datos para pruebas operativas
        $payloads = [
            // Tipo: Ofertas Volumétricas (Revisar mínimos en tabla precios)
            [
                'name' => 'Especial de Vinos Tintos de Altura',
                'type' => 'OFFER',
            ],
            [
                'name' => 'Pack de Cervezas Artesanales Importadas',
                'type' => 'OFFER',
            ],
            [
                'name' => 'Silo de Destilados Premium',
                'type' => 'OFFER',
            ],
            // Tipo: Plantillas de Agregado Rápido (Inyección masiva al carrito)
            [
                'name' => 'Plantilla: Kit Parrillero Fin de Semana',
                'type' => 'TEMPLATE',
            ],
            [
                'name' => 'Plantilla: Abastecimiento Mensual Oficina',
                'type' => 'TEMPLATE',
            ],
            [
                'name' => 'Plantilla: Base de Coctelería Clásica',
                'type' => 'TEMPLATE',
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($payloads as $payload) {
                $bundleId = Str::uuid()->toString();

                // 2. Persistencia del agrupador comercial
                DB::table('bundles')->insert([
                    'id'         => $bundleId,
                    'name'       => $payload['name'],
                    'type'       => $payload['type'],
                    'is_active'  => true,
                    'image_path' => null, // Se maneja como opcional en el ciclo de pruebas
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Mezclar el catálogo para asignar componentes aleatorios sin duplicados por grupo
                shuffle($skuIds);
                $componentsCount = rand(2, 5); // Cada combo tendrá entre 2 y 5 artículos hijos
                $selectedSkus = array_slice($skuIds, 0, $componentsCount);

                // 3. Persistencia de los componentes en la tabla pivote
                foreach ($selectedSkus as $skuId) {
                    DB::table('bundle_items')->insert([
                        'id'         => Str::uuid()->toString(),
                        'bundle_id'  => $bundleId,
                        'sku_id'     => $skuId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            $this->command->info("Seeder finalizado: 6 estructuras comerciales inyectadas con éxito (3 Ofertas / 3 Plantillas).");

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}