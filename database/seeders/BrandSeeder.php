<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Brand, Provider, Category};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use Throwable;

class BrandSeeder extends Seeder
{
    private string $sourceImageDir = 'data/images/brands';
    private string $destImageDir = 'img/brands';

    public function run(): void
    {
        $this->cleanDestinationDirectory();

        $csvPath = database_path('data/brands.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        // Carga de dependencias foráneas en memoria
        $providers = Provider::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();
            
        $categories = Category::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();

        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV vacío o ilegible.");
            return;
        }
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        $brandMap = []; // Mapa de jerarquía interna
        $rowNumber = 1;

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
                if (!$slug) throw new Exception("Fila {$rowNumber}: El slug de la marca es obligatorio.");

                // Resolución de Provider
                $providerSlug = strtolower($cleanRow['provider_slug'] ?? '');
                $providerId = $providers[$providerSlug] ?? null;
                if (!$providerId) {
                    throw new Exception("Fila {$rowNumber}: Provider '{$providerSlug}' no existe.");
                }

                // Resolución de Category
                $categorySlug = strtolower($cleanRow['category_slug'] ?? '');
                $categoryId = $categories[$categorySlug] ?? null;
                if (!$categoryId) {
                    throw new Exception("Fila {$rowNumber}: Category '{$categorySlug}' no existe.");
                }

                // Resolución de Jerarquía (Parent Brand)
                $parentId = null;
                $parentSlug = strtolower($cleanRow['parent_slug'] ?? '');
                if ($parentSlug) {
                    if (!array_key_exists($parentSlug, $brandMap)) {
                        throw new Exception("Fila {$rowNumber}: Marca padre '{$parentSlug}' no existe o no fue procesada antes.");
                    }
                    $parentId = $brandMap[$parentSlug];
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
                $brand = Brand::create([
                    'parent_id'   => $parentId,
                    'provider_id' => $providerId,
                    'category_id' => $categoryId,
                    'name'        => $cleanRow['name'],
                    'slug'        => $slug,
                    'bg_color'    => $cleanRow['bg_color'] ?? null,
                    'image_path'  => $imagePathForDb,
                    'website'     => $cleanRow['website'] ?? null,
                    'is_active'   => (bool) ($cleanRow['is_active'] ?? true),
                    'is_featured' => (bool) ($cleanRow['is_featured'] ?? false),
                    'sort_order'  => (int) ($cleanRow['sort_order'] ?? 0),
                    'description' => $cleanRow['description'] ?? null,
                ]);

                $brandMap[$slug] = $brand->id;
            }

            DB::commit();
            fclose($file);
            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " marcas procesadas.");

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