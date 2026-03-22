<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Sku, Product};
use Illuminate\Support\Facades\DB;

class SkuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Carga en RAM (O(1)) con normalización de case sensitivity
        $products = Product::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();

        $filePath = database_path('data/skus.csv');
        
        if (!file_exists($filePath)) {
            $this->command->warn("ARCHIVO NO ENCONTRADO: {$filePath}. Saltando sembrado de SKUs.");
            return;
        }

        $file = fopen($filePath, 'r');
        
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::transaction(function () use ($file, $headers, $products) {
            $rowNumber = 1;
            $skuOrder = 1;

            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rowNumber++;
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    throw new \Exception(
                        "ERROR FATAL DE ESTRUCTURA en fila {$rowNumber}.\n" .
                        "Esperaba " . count($headers) . " columnas, recibió " . count($data) . ".\n" .
                        "Data corrupta: " . json_encode($data)
                    );
                }

                $row = array_combine($headers, $data);

                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    if ($str === '') return null;
                    if (!mb_check_encoding($str, 'UTF-8')) {
                        $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
                    }
                    return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                $productSlug = strtolower(trim($cleanRow['product_slug'] ?? ''));
                $productId = $products[$productSlug] ?? null;

                if (!$productId) {
                    throw new \Exception(
                        "INTEGRIDAD REFERENCIAL COMPROMETIDA en fila {$rowNumber} ('{$cleanRow['name']}').\n" .
                        "- Product Slug ('{$productSlug}'): HUÉRFANO (No existe en la tabla products)"
                    );
                }

                // La llave de búsqueda para updateOrCreate en SKUs suele ser el código (EAN)
                // Si el código es nulo, usamos nombre + product_id como fallback temporal
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
    }
}