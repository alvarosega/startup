<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketZone;
use Illuminate\Support\Facades\DB;

class MarketZoneSeeder extends Seeder {
    public function run(): void {
        $filePath = database_path('data/market_zones.csv');
        if (!file_exists($filePath)) {
            $this->command->error("FILE_NOT_FOUND: {$filePath}");
            return;
        }

        $file = fopen($filePath, 'r');
        
        // REGLA 2.0.1: Aniquilación de BOM (Byte Order Mark) y espacios fantasma
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(function($h) {
            $clean = trim((string)$h);
            // Destruye el BOM UTF-8 invisible
            $clean = preg_replace('/^[\xef\xbb\xbf]+/', '', $clean);
            // Normaliza a minúsculas por si el CSV dice "Name" en vez de "name"
            return strtolower($clean);
        }, $rawHeaders);

        DB::transaction(function () use ($file, $headers) {
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                if (count($headers) !== count($data)) continue;
                
                $row = array_combine($headers, $data);

                // PROTOCOLO DE DESINFECCIÓN V2.0
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    if ($str === '') return null;

                    if (!mb_check_encoding($str, 'UTF-8')) {
                        $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
                    }
                    return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                // REGLA 2.B: Upsert basado en SLUG
                MarketZone::updateOrCreate(
                    ['slug' => $cleanRow['slug']], 
                    [
                        // Usamos fallback con null coalesce operator generalizado
                        'name'        => $cleanRow['name'] ?? 'ZONA_DESCONOCIDA', 
                        'hex_color'   => $cleanRow['hex_color'] ?? '#CCCCCC',
                        'svg_id'      => $cleanRow['svg_id'] ?? null,
                        'description' => $cleanRow['description'] ?? null,
                        'is_active'   => (bool)($cleanRow['is_active'] ?? true),
                    ]
                );
            }
        });

        fclose($file);
    }
}