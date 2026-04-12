<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sku, Product};
use Illuminate\Support\Facades\DB;

class SkuSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();

        $filePath = database_path('data/skus.csv');
        
        if (!file_exists($filePath)) {
            $this->command->warn("ARCHIVO NO ENCONTRADO: {$filePath}.");
            return;
        }

        $file = fopen($filePath, 'r');
        
        // Limpieza de cabeceras (BOM y espacios)
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::transaction(function () use ($file, $headers, $products) {
            $rowNumber = 1;
            $skuOrder = 1;

            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rowNumber++;
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    throw new \Exception("Error de estructura en fila {$rowNumber}. Columnas no coinciden.");
                }

                $row = array_combine($headers, $data);

                // Limpieza de codificación y espacios
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                $productSlug = strtolower(trim($cleanRow['product_slug'] ?? ''));
                $productId = $products[$productSlug] ?? null;

                if (!$productId) {
                    throw new \Exception("Producto '{$productSlug}' no encontrado en fila {$rowNumber}.");
                }

                $searchKey = !empty($cleanRow['code']) 
                    ? ['code' => $cleanRow['code']] 
                    : ['name' => $cleanRow['name'], 'product_id' => $productId];

                Sku::updateOrCreate(
                    $searchKey,
                    [
                        'product_id'        => $productId,
                        'name'              => $cleanRow['name'],
                        'base_price'        => $cleanRow['base_price'] ?? 0,
                        'weight'            => $cleanRow['weight'] ?? 0,
                        'conversion_factor' => $cleanRow['conversion_factor'] ?? 1,
                        'is_active'         => $cleanRow['is_active'] ?? 1,
                        'sort_order'        => $skuOrder++,
                    ]
                );
            }
        });

        fclose($file);
        $this->command->info('✅ SkuSeeder: SKUs procesados correctamente.');
    }
}