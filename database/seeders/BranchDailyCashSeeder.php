<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Operations\Branch;
use App\Models\Operations\BranchDailyCash;
use App\Models\Users\Admin;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class BranchDailyCashSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/branch_daily_cashes.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV de cajas vacío o ilegible.");
            fclose($file);
            return;
        }

        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);
        $fallbackAdmin = Admin::first();

        if (!$fallbackAdmin) {
            $this->command->error("CRÍTICO: No existe ningún administrador para asociar las cajas.");
            fclose($file);
            return;
        }

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
                $cleanRow = array_map(function ($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                if (empty($cleanRow['branch_slug']) || empty($cleanRow['date']) || !isset($cleanRow['opening_amount'])) {
                    throw new Exception("Fila {$rowNumber}: 'branch_slug', 'date' y 'opening_amount' son obligatorios.");
                }

                $branch = Branch::where('slug', $cleanRow['branch_slug'])->first();
                if (!$branch) {
                    throw new Exception("Fila {$rowNumber}: Sucursal con slug '{$cleanRow['branch_slug']}' no encontrada.");
                }

                $admin = !empty($cleanRow['admin_email'])
                    ? (Admin::where('email', $cleanRow['admin_email'])->first() ?? $fallbackAdmin)
                    : $fallbackAdmin;

                $isClosed = ($cleanRow['status'] ?? 'closed') === 'closed';

                BranchDailyCash::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'date'      => $cleanRow['date'],
                    ],
                    [
                        'opened_by_admin_id' => $admin->id,
                        'closed_by_admin_id' => $isClosed ? $admin->id : null,
                        'opening_amount'     => (float) $cleanRow['opening_amount'],
                        'expected_amount'    => isset($cleanRow['expected_amount']) ? (float) $cleanRow['expected_amount'] : 0.00,
                        'actual_amount'      => isset($cleanRow['actual_amount']) ? (float) $cleanRow['actual_amount'] : null,
                        'difference'         => isset($cleanRow['difference']) ? (float) $cleanRow['difference'] : null,
                        'status'             => $cleanRow['status'] ?? 'closed',
                        'closing_notes'      => $cleanRow['closing_notes'] ?? null,
                        'opened_at'          => $cleanRow['opened_at'] ?? now(),
                        'closed_at'          => $isClosed ? ($cleanRow['closed_at'] ?? now()) : null,
                    ]
                );
            }

            DB::commit();
            fclose($file);

            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " registros de caja sembrados desde CSV.");
        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO EN FILA {$rowNumber}: " . $e->getMessage());
            throw $e;
        }
    }
}