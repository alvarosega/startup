<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Brand, Provider, Category, MarketZone};
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder {
    public function run(): void {
        // Convierte los nombres comerciales a minúsculas para evitar fallos por "Case Sensitivity"
        $providers = Provider::pluck('id', 'commercial_name')
        ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
        ->toArray();
        $categories = Category::pluck('id', 'slug')->toArray();
        $zones = MarketZone::pluck('id', 'slug')->toArray();

        $file = fopen(database_path('data/brands.csv'), 'r');
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);
        
        DB::transaction(function () use ($file, $headers, $providers, $categories, $zones) {
            $rowNumber = 1; // Para saber en qué línea falla

            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rowNumber++;

                // 1. Ignorar líneas completamente vacías (típico al final del CSV)
                if (empty(array_filter($data))) continue;

                // 2. AUDITORÍA ESTRICTA: Si las columnas no coinciden, que el sistema explote y nos diga por qué.
                if (count($headers) !== count($data)) {
                    throw new \Exception(
                        "ERROR DE FORMATO CSV en la fila {$rowNumber}:\n" .
                        "Esperaba " . count($headers) . " columnas, pero recibió " . count($data) . ".\n" .
                        "Asegúrate de que el delimitador sea punto y coma (;) y no haya comas extraviadas.\n" .
                        "Datos recibidos: " . json_encode($data)
                    );
                }

                $row = array_combine($headers, $data);

                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    if ($str === '') return null;
                    if (!mb_check_encoding($str, 'UTF-8')) {
                        $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
                    }
                    return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
                }, $row);

                $providerId = $providers[strtolower(trim($cleanRow['provider_ref'] ?? ''))] ?? null;
                $categoryId = $categories[$cleanRow['category_slug'] ?? ''] ?? null;
                $zoneId = $zones[$cleanRow['market_zone_slug'] ?? ''] ?? null;

                if (!$providerId || !$categoryId || !$zoneId) {
                    throw new \Exception(
                        "Fallo de integridad referencial en CSV para la fila '{$cleanRow['name']}'.\n" .
                        "Faltan coincidencias en la BD:\n" .
                        "- Provider ('" . ($cleanRow['provider_ref'] ?? 'VACIO') . "'): " . ($providerId ? 'OK' : 'NO ENCONTRADO') . "\n" .
                        "- Category ('" . ($cleanRow['category_slug'] ?? 'VACIO') . "'): " . ($categoryId ? 'OK' : 'NO ENCONTRADO') . "\n" .
                        "- Market Zone ('" . ($cleanRow['market_zone_slug'] ?? 'VACIO') . "'): " . ($zoneId ? 'OK' : 'NO ENCONTRADO')
                    );
                }

                Brand::updateOrCreate(['slug' => $cleanRow['slug']], [
                    'name'           => $cleanRow['name'], 
                    'provider_id'    => $providerId,
                    'category_id'    => $categoryId, 
                    'market_zone_id' => $zoneId, 
                    'is_active'      => $cleanRow['is_active'] ?? 1,
                ]);
            }
        });
        fclose($file);
    }
}