<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Price, Sku, Branch};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Throwable;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/prices.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        // Carga de dependencias foráneas en memoria
        $skus = Sku::whereNotNull('code')->pluck('id', 'code')->toArray();
        $branches = Branch::pluck('id', 'slug')
            ->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])
            ->toArray();

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

                // Resolución de SKU
                $skuCode = $cleanRow['sku_code'] ?? null;
                if (!$skuCode || !isset($skus[$skuCode])) {
                    throw new Exception("Fila {$rowNumber}: SKU code '{$skuCode}' no existe o está vacío.");
                }

                // Resolución de Branch
                $branchSlug = strtolower($cleanRow['branch_slug'] ?? '');
                if (!$branchSlug || !isset($branches[$branchSlug])) {
                    throw new Exception("Fila {$rowNumber}: Branch slug '{$branchSlug}' no existe o está vacío.");
                }

                // Gestión de fechas
                $validFrom = $cleanRow['valid_from'] ? Carbon::parse($cleanRow['valid_from']) : now();
                $validTo = $cleanRow['valid_to'] ? Carbon::parse($cleanRow['valid_to']) : null;

                if (!isset($cleanRow['type']) || !isset($cleanRow['final_price'])) {
                    throw new Exception("Fila {$rowNumber}: 'type' y 'final_price' son campos obligatorios.");
                }

                // Persistencia
                Price::create([
                    'sku_id'        => $skus[$skuCode],
                    'branch_id'     => $branches[$branchSlug],
                    'type'          => $cleanRow['type'],
                    'list_price'    => (float) ($cleanRow['list_price'] ?? 0),
                    'final_price'   => (float) $cleanRow['final_price'],
                    'min_quantity'  => (int) ($cleanRow['min_quantity'] ?? 1),
                    'priority'      => (int) ($cleanRow['priority'] ?? 1),
                    'valid_from'    => $validFrom,
                    'valid_to'      => $validTo,
                    'created_by_id' => null,
                    'updated_by_id' => null,
                ]);
            }

            DB::commit();
            fclose($file);
            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " precios procesados.");

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO: " . $e->getMessage());
            throw $e;
        }
    }
}