<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalog\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use Throwable;

class CategorySeeder extends Seeder
{
    /**
     * Rutas de origen físico en el proyecto.
     */
    private string $sourceImageDir = 'data/images/categories';
    private string $sourceIconDir = 'data/icons/categories';

    /**
     * Rutas de destino en el disco public (storage/app/public/).
     */
    private string $destImageDir = 'img/categories';
    private string $destIconDir = 'icons/categories';

    public function run(): void
    {
        // 1. Preparación y limpieza absoluta
        $this->cleanDestinationDirectories();

        $csvPath = database_path('data/categories.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        $file = fopen($csvPath, 'r');
        
        // Extracción de cabeceras y limpieza de BOM UTF-8
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: El archivo CSV está vacío o es ilegible.");
            return;
        }
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        // Mapa en memoria para resolver la jerarquía (padres a hijos)
        $categoryMap = [];
        $rowNumber = 1;

        // 2. Inicio de Transacción
        DB::beginTransaction();

        try {
            while (($data = fgetcsv($file, 0, ',')) !== false) {
                $rowNumber++;
                
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    throw new Exception("Error de estructura en fila {$rowNumber}. Número de columnas inconsistente.");
                }

                $row = array_combine($headers, $data);

                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                $slug = strtolower($cleanRow['slug'] ?? '');
                if (!$slug) {
                    throw new Exception("Fila {$rowNumber}: El slug es obligatorio.");
                }

                // Resolución estricta de la jerarquía
                $parentId = null;
                $parentSlug = strtolower($cleanRow['parent_slug'] ?? '');
                
                if ($parentSlug) {
                    if (!array_key_exists($parentSlug, $categoryMap)) {
                        throw new Exception("Fila {$rowNumber}: Dependencia rota. El padre '{$parentSlug}' no existe o no fue insertado antes que el hijo.");
                    }
                    $parentId = $categoryMap[$parentSlug];
                }

                // Procesamiento Multimedia: Imagen
                $imagePathForDb = null;
                $imageFile = $cleanRow['image_file'] ?? null;
                if ($imageFile) {
                    $sourceImagePath = database_path($this->sourceImageDir . '/' . $imageFile);
                    if (!file_exists($sourceImagePath)) {
                        throw new Exception("Fila {$rowNumber}: Imagen física '{$imageFile}' no encontrada en el origen.");
                    }
                    $imagePathForDb = $this->destImageDir . '/' . $imageFile;
                    Storage::disk('public')->put($imagePathForDb, file_get_contents($sourceImagePath));
                }

                // Procesamiento Multimedia: Icono
                $iconPathForDb = null;
                $iconFile = $cleanRow['icon_file'] ?? null;
                if ($iconFile) {
                    $sourceIconPath = database_path($this->sourceIconDir . '/' . $iconFile);
                    if (!file_exists($sourceIconPath)) {
                        throw new Exception("Fila {$rowNumber}: Icono físico '{$iconFile}' no encontrado en el origen.");
                    }
                    $iconPathForDb = $this->destIconDir . '/' . $iconFile;
                    Storage::disk('public')->put($iconPathForDb, file_get_contents($sourceIconPath));
                }

                // Persistencia (omitimos 'version' para que la base de datos asigne el 0 por defecto)
                $category = Category::create([
                    'parent_id'          => $parentId,
                    'name'               => $cleanRow['name'],
                    'slug'               => $slug,
                    'external_code'      => $cleanRow['external_code'] ?? null,
                    'tax_classification' => $cleanRow['tax_classification'] ?? null,
                    'requires_age_check' => (bool) ($cleanRow['requires_age_check'] ?? false),
                    'is_active'          => (bool) ($cleanRow['is_active'] ?? true),
                    'is_featured'        => (bool) ($cleanRow['is_featured'] ?? false),
                    'sort_order'         => (int) ($cleanRow['sort_order'] ?? 0),
                    'image_path'         => $imagePathForDb,
                    'icon_path'          => $iconPathForDb,
                    'bg_color'           => $cleanRow['bg_color'] ?? null,
                    'description'        => $cleanRow['description'] ?? null,
                    'seo_title'          => $cleanRow['seo_title'] ?? null,
                    'seo_description'    => $cleanRow['seo_description'] ?? null,
                ]);

                // Registrar en memoria para los hijos que vengan en las siguientes filas
                $categoryMap[$slug] = $category->id;
            }

            // Cierre exitoso
            DB::commit();
            fclose($file);
            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " categorías procesadas correctamente.");

        } catch (Throwable $e) {
            // Rollback Total (Datos y Archivos)
            DB::rollBack();
            if (is_resource($file)) {
                fclose($file);
            }
            
            $this->cleanDestinationDirectories();
            
            $this->command->error("ABORTO DEL SEEDER: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vacía ambos directorios de destino en el disco public.
     */
    private function cleanDestinationDirectories(): void
    {
        $disk = Storage::disk('public');
        
        if ($disk->exists($this->destImageDir)) {
            $disk->deleteDirectory($this->destImageDir);
        }
        $disk->makeDirectory($this->destImageDir);

        if ($disk->exists($this->destIconDir)) {
            $disk->deleteDirectory($this->destIconDir);
        }
        $disk->makeDirectory($this->destIconDir);
    }
}