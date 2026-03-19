<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/categories.csv');
        
        if (!file_exists($filePath)) {
            return;
        }

        $file = fopen($filePath, 'r');
        
        // 1. Protocolo Anti-BOM y Normalización de Cabeceras
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::transaction(function () use ($file, $headers) {
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                if (count($headers) !== count($data)) continue;
                
                $row = array_combine($headers, $data);

                // 2. Sanitización de Codificación (The Law: Anti-Corruption)
                $cleanRow = array_map(function($value) {
                    $str = trim((string)$value);
                    if ($str === '') return null;
                    
                    // Si detecta encoding corrupto (ISO), fuerza a UTF-8
                    if (!mb_check_encoding($str, 'UTF-8')) {
                        $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
                    }
                    return $str;
                }, $row);


                    Category::updateOrCreate(
                        ['slug' => $cleanRow['slug'] ?? Str::slug($cleanRow['name'])],
                        [
                            'name'               => $cleanRow['name'],
                            'external_code'      => $cleanRow['external_code'] ?? null,
                            'tax_classification' => $cleanRow['tax_classification'] ?? null,
                            'requires_age_check' => (bool) ($cleanRow['requires_age_check'] ?? 0),
                            'is_active'          => (bool) ($cleanRow['is_active'] ?? 1),
                            'is_featured'        => (bool) ($cleanRow['is_featured'] ?? 0),
                            
                            // REGLA DE NEGOCIO: 1 es principal, prioridad ascendente.
                            'sort_order'         => (int) ($cleanRow['sort_order'] ?? 99),
                            
                            'bg_color'           => $cleanRow['bg_color'] ?? null, 
                        ]
                    );
            }
        });

        fclose($file);
    }
}