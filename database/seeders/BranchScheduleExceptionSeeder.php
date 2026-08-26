<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Operations\Branch;
use App\Models\Operations\BranchScheduleException;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class BranchScheduleExceptionSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/branch_schedule_exceptions.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$csvPath}.");
            return;
        }

        $file = fopen($csvPath, 'r');
        $rawHeaders = fgetcsv($file, 0, ',');
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: CSV de excepciones vacío o ilegible.");
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
                $cleanRow = array_map(function ($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                if (empty($cleanRow['branch_slug']) || empty($cleanRow['date'])) {
                    throw new Exception("Fila {$rowNumber}: 'branch_slug' y 'date' son obligatorios.");
                }

                $branch = Branch::where('slug', $cleanRow['branch_slug'])->first();
                if (!$branch) {
                    throw new Exception("Fila {$rowNumber}: Sucursal con slug '{$cleanRow['branch_slug']}' no encontrada.");
                }

                BranchScheduleException::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'date'      => $cleanRow['date'],
                    ],
                    [
                        'opening_time' => $cleanRow['opening_time'] ?? null,
                        'closing_time' => $cleanRow['closing_time'] ?? null,
                        'is_closed'    => (bool) ($cleanRow['is_closed'] ?? false),
                        'reason'       => $cleanRow['reason'] ?? null,
                    ]
                );
            }

            DB::commit();
            fclose($file);

            $this->command->info("ÉXITO: " . ($rowNumber - 1) . " excepciones de horario sembradas desde CSV.");
        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("ABORTO EN FILA {$rowNumber}: " . $e->getMessage());
            throw $e;
        }
    }
}