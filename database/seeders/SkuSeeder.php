<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalog\Sku;
use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Throwable;

class SkuSeeder extends Seeder
{
    /**
     * Directorio de origen físico de las imágenes.
     */
    private string $sourceImageDir = 'data/images/skus';

    /**
     * Directorio de destino en el disco public (storage/app/public/).
     */
    private string $destImageDir = 'img/skus';

    public function run(): void
    {
        // 1. Preparación del Entorno
        $this->cleanDestinationDirectory();

        $csvPath = database_path('data/skus.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        // Carga de catálogo en memoria para validación rápida
        $products = Product::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();

        $file = fopen($csvPath, 'r');
        
        // Extracción y limpieza de cabeceras (eliminación de BOM UTF-8)
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: El archivo CSV está vacío o es ilegible.");
            return;
        }
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        $rowNumber = 1;

        // 2. Inicio de Transacción
        DB::beginTransaction();

        try {
            while (($data = fgetcsv($file, 0, ',')) !== false) {
                $rowNumber++;
                
                // Omitir líneas completamente vacías
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    throw new Exception("Error de estructura en fila {$rowNumber}. Número de columnas inconsistente.");
                }

                $row = array_combine($headers, $data);

                // Limpieza básica de la fila
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                // Validación 1: Existencia de Producto
                $productSlug = strtolower($cleanRow['product_slug'] ?? '');
                $productId = $products[$productSlug] ?? null;

                if (!$productId) {
                    throw new Exception("Fila {$rowNumber}: Producto '{$productSlug}' huérfano o no existe en la base de datos.");
                }

                // Procesamiento y Validación 2: Imagen
                $imagePathForDb = null;
                $imageFile = $cleanRow['image_file'] ?? null;

                if ($imageFile) {
                    $sourceImagePath = database_path($this->sourceImageDir . '/' . $imageFile);
                    
                    if (!file_exists($sourceImagePath)) {
                        throw new Exception("Fila {$rowNumber}: Archivo físico '{$imageFile}' no encontrado en " . database_path($this->sourceImageDir));
                    }

                    // Copiado físico de la imagen
                    $imagePathForDb = $this->destImageDir . '/' . $imageFile;
                    Storage::disk('public')->put(
                        $imagePathForDb,
                        file_get_contents($sourceImagePath)
                    );
                }

                // 4. Persistencia (Inserción directa y estricta)
                Sku::create([
                    'product_id'        => $productId,
                    'name'              => $cleanRow['name'],
                    'code'              => $cleanRow['code'],
                    'image_path'        => $imagePathForDb,
                    'base_price'        => (float) ($cleanRow['base_price'] ?? 0),
                    'weight'            => (float) ($cleanRow['weight'] ?? 0),
                    'conversion_factor' => (float) ($cleanRow['conversion_factor'] ?? 1),
                    'is_active'         => (bool) ($cleanRow['is_active'] ?? true),
                    'sort_order'        => (int) ($cleanRow['sort_order'] ?? 0),
                ]);
            }

            // 6. Cierre exitoso
            DB::commit();
            fclose($file);
            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " SKUs procesados e insertados correctamente.");

        } catch (Throwable $e) {
            // 5. Manejo de Errores (Rollback Total)
            DB::rollBack();
            if (is_resource($file)) {
                fclose($file);
            }
            
            // Revertir operaciones de archivos
            $this->cleanDestinationDirectory();
            
            $this->command->error("ABORTO DEL SEEDER: " . $e->getMessage());
            $this->command->error("Se ha revertido la base de datos y se han eliminado las imágenes huérfanas.");
            
            // Opcional: lanzar la excepción si quieres que el comando 'migrate:fresh --seed' falle visiblemente
            throw $e;
        }
    }

    /**
     * Vacía el directorio de destino para garantizar idempotencia y limpieza tras errores.
     */
    private function cleanDestinationDirectory(): void
    {
        $disk = Storage::disk('public');
        
        if ($disk->exists($this->destImageDir)) {
            $disk->deleteDirectory($this->destImageDir);
        }
        
        $disk->makeDirectory($this->destImageDir);
    }
}