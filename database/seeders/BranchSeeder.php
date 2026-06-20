<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;
use Throwable;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/branches.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV vacío o ilegible.");
            return;
        }
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

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

                if (empty($cleanRow['slug']) || empty($cleanRow['name'])) {
                    throw new Exception("Fila {$rowNumber}: Campos 'name' y 'slug' son obligatorios.");
                }

                // Validación estricta de JSON
                $coveragePolygon = null;
                if ($cleanRow['coverage_polygon']) {
                    $coveragePolygon = json_decode($cleanRow['coverage_polygon'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Fila {$rowNumber}: JSON inválido en 'coverage_polygon'.");
                    }
                }

                $openingHours = null;
                if ($cleanRow['opening_hours']) {
                    $openingHours = json_decode($cleanRow['opening_hours'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Fila {$rowNumber}: JSON inválido en 'opening_hours'.");
                    }
                }

                // LEY: No se envía ID. El sistema inyecta el UUID aleatorio/dinámico por fila.
                Branch::create([
                    'name'                          => $cleanRow['name'],
                    'slug'                          => strtolower($cleanRow['slug']),
                    'city'                          => $cleanRow['city'] ?? 'La Paz',
                    'phone'                         => $cleanRow['phone'] ?? null,
                    'address'                       => $cleanRow['address'] ?? null,
                    'latitude'                      => isset($cleanRow['latitude']) ? (float) $cleanRow['latitude'] : null,
                    'longitude'                     => isset($cleanRow['longitude']) ? (float) $cleanRow['longitude'] : null,
                    'coverage_polygon'              => $coveragePolygon,
                    'opening_hours'                 => $openingHours,
                    'delivery_base_fee'             => (float) ($cleanRow['delivery_base_fee'] ?? 0.00),
                    'delivery_price_per_km'         => (float) ($cleanRow['delivery_price_per_km'] ?? 0.00),
                    'surge_multiplier'              => (float) ($cleanRow['surge_multiplier'] ?? 1.00),
                    'min_order_amount'              => (float) ($cleanRow['min_order_amount'] ?? 0.00),
                    'small_order_fee'               => (float) ($cleanRow['small_order_fee'] ?? 0.00),
                    'base_service_fee_percentage'   => (float) ($cleanRow['base_service_fee_percentage'] ?? 0.00),
                    'is_default'                    => (bool) ($cleanRow['is_default'] ?? false),
                    'is_active'                     => (bool) ($cleanRow['is_active'] ?? true),
                ]);
            }

            DB::commit();
            fclose($file);

            // AUTOMATIZACIÓN DE PURGA: Destruye los punteros obsoletos en memoria inmediatamente después del commit
            Cache::forget('active_branch_polygons');
            Cache::forget('shop_default_branch_id');

            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " sucursales sembradas. Memoria caché invalidada correctamente.");

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO: " . $e->getMessage());
            throw $e;
        }
    }
}