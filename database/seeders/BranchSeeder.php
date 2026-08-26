<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Operations\Branch;
use App\Models\Operations\BranchSchedule;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            fclose($file);
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

                if (!isset($cleanRow['latitude'], $cleanRow['longitude'])) {
                    throw new Exception("Fila {$rowNumber}: Coordenadas 'latitude' y 'longitude' son obligatorias.");
                }

                $latitude = (float) $cleanRow['latitude'];
                $longitude = (float) $cleanRow['longitude'];

                // WKT POINT con orden explícito: Longitud Latitud
                $locationExpression = DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})', 4326, 'axis-order=long-lat')");

                // CSV contiene [[lat, lng], ...] -> WKT POLYGON exige "longitud latitud"
                $coveragePolygonExpression = null;
                if (!empty($cleanRow['coverage_polygon'])) {
                    $polyData = json_decode($cleanRow['coverage_polygon'], true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($polyData)) {
                        throw new Exception("Fila {$rowNumber}: JSON inválido en 'coverage_polygon'.");
                    }

                    $wktPoints = array_map(fn($point) => "{$point[1]} {$point[0]}", $polyData);

                    if (reset($wktPoints) !== end($wktPoints)) {
                        $wktPoints[] = reset($wktPoints);
                    }

                    $wktPolygonString = "POLYGON((" . implode(',', $wktPoints) . "))";
                    $coveragePolygonExpression = DB::raw("ST_GeomFromText('{$wktPolygonString}', 4326, 'axis-order=long-lat')");
                } else {
                    throw new Exception("Fila {$rowNumber}: El campo 'coverage_polygon' es obligatorio.");
                }

                $branch = Branch::create([
                    'name'                        => $cleanRow['name'],
                    'slug'                        => strtolower($cleanRow['slug']),
                    'city'                        => $cleanRow['city'] ?? 'La Paz',
                    'phone'                       => $cleanRow['phone'] ?? null,
                    'address'                     => $cleanRow['address'] ?? null,
                    'location'                    => $locationExpression,
                    'coverage_polygon'            => $coveragePolygonExpression,
                    'delivery_base_fee'           => (float) ($cleanRow['delivery_base_fee'] ?? 0.00),
                    'delivery_price_per_km'       => (float) ($cleanRow['delivery_price_per_km'] ?? 0.00),
                    'surge_multiplier'            => (float) ($cleanRow['surge_multiplier'] ?? 1.00),
                    'min_order_amount'            => (float) ($cleanRow['min_order_amount'] ?? 0.00),
                    'small_order_fee'             => (float) ($cleanRow['small_order_fee'] ?? 0.00),
                    'base_service_fee_percentage' => (float) ($cleanRow['base_service_fee_percentage'] ?? 0.00),
                    'is_default'                  => (bool) ($cleanRow['is_default'] ?? false),
                    'is_active'                   => (bool) ($cleanRow['is_active'] ?? true),
                    'is_operational_paused'       => false,
                    'max_active_orders'           => isset($cleanRow['max_active_orders']) && $cleanRow['max_active_orders'] !== '' ? (int) $cleanRow['max_active_orders'] : null,
                    'deleted_epoch'               => 0,
                ]);

                for ($day = 0; $day <= 6; $day++) {
                    BranchSchedule::create([
                        'branch_id'    => $branch->id,
                        'day_of_week'  => $day,
                        'opening_time' => '08:00:00',
                        'closing_time' => '23:00:00',
                        'is_closed'    => false,
                    ]);
                }
            }

            DB::commit();
            fclose($file);

            Cache::forget('active_branch_polygons');
            Cache::forget('shop_default_branch_id');

            $this->command->info("ÉXITO: Sucursales y geometrías sembradas correctamente.");
        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO EN FILA {$rowNumber}: " . $e->getMessage());
            throw $e;
        }
    }
}