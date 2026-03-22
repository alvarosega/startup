<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Product, Brand, Category};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();
    
        $categories = Category::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();

        $filePath = database_path('data/products.csv');
        
        if (!file_exists($filePath)) {
            $this->command->warn("ARCHIVO NO ENCONTRADO: {$filePath}. Saltando sembrado de productos maestros.");
            return;
        }

        $file = fopen($filePath, 'r');
        
        // Limpiar BOM (Byte Order Mark) oculto de Excel
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::transaction(function () use ($file, $headers, $brands, $categories) {
            $rowNumber = 1;
            $productOrder = 1;

            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rowNumber++;
                if (empty(array_filter($data))) continue;

                // Auditoría de Estructura CSV
                if (count($headers) !== count($data)) {
                    throw new \Exception(
                        "ERROR FATAL DE ESTRUCTURA en fila {$rowNumber}.\n" .
                        "Esperaba " . count($headers) . " columnas, recibió " . count($data) . ".\n" .
                        "Data corrupta: " . json_encode($data)
                    );
                }

                $row = array_combine($headers, $data);

                // Protocolo Anti-Corrupción UTF-8
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    if ($str === '') return null;
                    if (!mb_check_encoding($str, 'UTF-8')) {
                        $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
                    }
                    return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                // Normalización de slugs de búsqueda
                $brandSlug = strtolower($cleanRow['brand_slug'] ?? '');
                $categorySlug = strtolower($cleanRow['category_slug'] ?? '');

                // Extracción de llaves foráneas
                $brandId = $brands[$brandSlug] ?? null;
                $categoryId = $categories[$categorySlug] ?? null;

                // Auditoría de Integridad Relacional
                if (!$brandId || !$categoryId) {
                    throw new \Exception(
                        "INTEGRIDAD REFERENCIAL COMPROMETIDA en fila {$rowNumber} ('{$cleanRow['name']}').\n" .
                        "- Brand Slug ('{$brandSlug}'): " . ($brandId ? 'OK' : 'HUÉRFANO') . "\n" .
                        "- Category Slug ('{$categorySlug}'): " . ($categoryId ? 'OK' : 'HUÉRFANO')
                    );
                }

                $nameClean = trim(mb_strtolower($cleanRow['name']));
                $productSlug = str_replace(' ', '-', $nameClean);
                $productSlug = preg_replace('/[^a-z0-9ñ\-]/u', '', $productSlug);
                
                // Persistencia
                Product::updateOrCreate(
                    ['slug' => $productSlug], 
                    [
                        'name'         => $cleanRow['name'],
                        'brand_id'     => $brandId,
                        'category_id'  => $categoryId,
                        'description'  => $cleanRow['description'] ?? null,
                        'is_active'    => $cleanRow['is_active'] ?? 1,
                        'is_alcoholic' => $cleanRow['is_alcoholic'] ?? 0,
                        'sort_order'   => $productOrder++,
                    ]
                );
            }
        });

        fclose($file);
    }
}