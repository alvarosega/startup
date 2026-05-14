<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Product, Brand, Category};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use Throwable;

class ProductSeeder extends Seeder
{
    private string $sourceImageDir = 'data/images/products';
    private string $destImageDir = 'img/products';

    public function run(): void
    {
        // 1. Preparación y limpieza absoluta
        $this->cleanDestinationDirectory();

        $csvPath = database_path('data/products.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        // Carga de dependencias foráneas en memoria
        $brands = Brand::pluck('id', 'slug')
            ->mapWithKeys(function ($id, $slug) {
                // Forzamos $slug a string para que trim() no falle
                $cleanSlug = strtolower(trim((string)$slug));
                return [$cleanSlug => $id];
            })
            ->toArray();
            
        $categories = Category::pluck('id', 'slug')
            ->mapWithKeys(function ($id, $slug) {
                // Forzamos $slug a string aquí también
                $cleanSlug = strtolower(trim((string)$slug));
                return [$cleanSlug => $id];
            })
        ->toArray();
        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV vacío o ilegible.");
            return;
        }
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        $rowNumber = 1;

        // 2. Inicio de Transacción
        DB::beginTransaction();

        try {
            while (($data = fgetcsv($file, 0, ',')) !== false) {
                $rowNumber++;
                
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    throw new Exception("Error en fila {$rowNumber}: Número de columnas inconsistente.");
                }

                $row = array_combine($headers, $data);
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                $slug = strtolower($cleanRow['slug'] ?? '');
                if (!$slug) throw new Exception("Fila {$rowNumber}: El slug del producto es obligatorio.");

                // Resolución de Brand
                $brandSlug = strtolower($cleanRow['brand_slug'] ?? '');
                $brandId = $brands[$brandSlug] ?? null;
                if (!$brandId) {
                    throw new Exception("Fila {$rowNumber}: Brand '{$brandSlug}' no existe.");
                }

                // Resolución de Category
                $categorySlug = strtolower($cleanRow['category_slug'] ?? '');
                $categoryId = $categories[$categorySlug] ?? null;
                if (!$categoryId) {
                    throw new Exception("Fila {$rowNumber}: Category '{$categorySlug}' no existe.");
                }

                // Procesamiento de Imagen
                $imagePathForDb = null;
                $imageFile = $cleanRow['image_file'] ?? null;
                if ($imageFile) {
                    $sourceImagePath = database_path($this->sourceImageDir . '/' . $imageFile);
                    if (!file_exists($sourceImagePath)) {
                        throw new Exception("Fila {$rowNumber}: Imagen física '{$imageFile}' no encontrada.");
                    }
                    $imagePathForDb = $this->destImageDir . '/' . $imageFile;
                    Storage::disk('public')->put($imagePathForDb, file_get_contents($sourceImagePath));
                }

                // Persistencia
                Product::create([
                    'brand_id'     => $brandId,
                    'category_id'  => $categoryId,
                    'name'         => $cleanRow['name'],
                    'slug'         => $slug,
                    'is_featured'  => isset($cleanRow['is_featured']) ? (bool) $cleanRow['is_featured'] : true,
                    'description'  => $cleanRow['description'] ?? null,
                    'image_path'   => $imagePathForDb,
                    'is_active'    => isset($cleanRow['is_active']) ? (bool) $cleanRow['is_active'] : true,
                    'is_alcoholic' => (bool) ($cleanRow['is_alcoholic'] ?? false),
                    'sort_order'   => (int) ($cleanRow['sort_order'] ?? 0),
                ]);
            }

            DB::commit();
            fclose($file);
            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " productos procesados.");

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            
            $this->cleanDestinationDirectory();
            
            $this->command->error("ABORTO: " . $e->getMessage());
            throw $e;
        }
    }

    private function cleanDestinationDirectory(): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists($this->destImageDir)) {
            $disk->deleteDirectory($this->destImageDir);
        }
        $disk->makeDirectory($this->destImageDir);
    }
}