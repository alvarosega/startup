<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketZone;
use Illuminate\Support\Facades\DB;
use Throwable;

class MarketZoneSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/market_zones.csv');
        
        if (!file_exists($filePath)) {
            $this->command->error("CRÍTICO: Archivo CSV no encontrado en {$filePath}.");
            return;
        }

        $file = fopen($filePath, 'r');
        // Protocolo: Delimitador por coma (,) según tu último ejemplo
        $rawHeaders = fgetcsv($file, 0, ',');
        
        if (!$rawHeaders) {
            $this->command->error("CRÍTICO: El archivo CSV está vacío.");
            fclose($file);
            return;
        }

        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::beginTransaction();

        try {
            $rowNumber = 1;

            while (($data = fgetcsv($file, 0, ',')) !== false) {
                $rowNumber++;
                if (empty(array_filter($data))) continue;

                if (count($headers) !== count($data)) {
                    $this->command->error("ERROR DE ESTRUCTURA en fila {$rowNumber}. Columnas: " . count($data) . " vs Headers: " . count($headers));
                    continue;
                }

                $row = array_combine($headers, $data);
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    return $str === '' ? null : mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                // RESOLUCIÓN DE MAPEO: El CSV usa 'bg_color', la DB usa 'hex_color'
                $rawColor = $cleanRow['bg_color'] ?? 'CCCCCC';
                $hexColor = '#' . ltrim((string)$rawColor, '#');

                MarketZone::updateOrCreate(
                    ['slug' => $cleanRow['slug'] ?? throw new \Exception("Fila {$rowNumber} sin slug.")],
                    [
                        'name'        => $cleanRow['name'] ?? 'Sin Nombre',
                        'sort_order'  => (int)($cleanRow['sort_order'] ?? 0),
                        'hex_color'   => $hexColor,
                        'description' => null, // Campo no presente en el CSV actual
                        'is_active'   => (bool)($cleanRow['is_active'] ?? 1),
                    ]
                );
            }

            DB::commit();
            fclose($file);
            $this->command->info('Proceso de sembrado de MarketZones finalizado con éxito.');

        } catch (Throwable $e) {
            DB::rollBack();
            if (is_resource($file)) fclose($file);
            $this->command->error("FALLO CRÍTICO en MarketZoneSeeder: " . $e->getMessage());
            throw $e;
        }
    }
}