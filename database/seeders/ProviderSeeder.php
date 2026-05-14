<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/providers.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: El archivo CSV está vacío o es ilegible.");
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

                if (empty($cleanRow['slug']) || empty($cleanRow['tax_id'])) {
                    throw new Exception("Fila {$rowNumber}: Campos 'slug' y 'tax_id' son obligatorios.");
                }

                Provider::create([
                    'company_name'    => $cleanRow['company_name'],
                    'commercial_name' => $cleanRow['commercial_name'] ?? null,
                    'slug'            => strtolower($cleanRow['slug']),
                    'tax_id'          => $cleanRow['tax_id'],
                    'internal_code'   => $cleanRow['internal_code'] ?? null,
                    'contact_name'    => $cleanRow['contact_name'] ?? null,
                    'email_orders'    => $cleanRow['email_orders'] ?? null,
                    'phone'           => $cleanRow['phone'] ?? null,
                    'address'         => $cleanRow['address'] ?? null,
                    'city'            => $cleanRow['city'] ?? null,
                    'lead_time_days'  => (int) ($cleanRow['lead_time_days'] ?? 1),
                    'min_order_value' => (float) ($cleanRow['min_order_value'] ?? 0),
                    'credit_days'     => (int) ($cleanRow['credit_days'] ?? 0),
                    'credit_limit'    => (float) ($cleanRow['credit_limit'] ?? 0),
                    'is_active'       => (bool) ($cleanRow['is_active'] ?? true),
                    'notes'           => $cleanRow['notes'] ?? null,
                ]);
            }

            DB::commit();
            fclose($file);
            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " proveedores procesados.");

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO: " . $e->getMessage());
            throw $e;
        }
    }
}