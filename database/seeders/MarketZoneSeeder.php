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
        $rawHeaders = fgetcsv($file, 0, ';');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), $rawHeaders);

        DB::transaction(function () use ($file, $headers) {
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                if (count($headers) !== count($data)) continue;
                $row = array_combine($headers, $data);

                MarketZone::updateOrCreate(
                    ['slug' => trim($row['slug'])], 
                    [
                        'name'       => trim($row['name']), 
                        'hex_color'  => isset($row['bg_color']) ? '#' . ltrim(trim($row['bg_color']), '#') : '#CCCCCC',
                        'sort_order' => (int)($row['sort_order'] ?? 0),
                        'is_active'  => (bool)($row['is_active'] ?? true),
                    ]
                );
            }
        });
        fclose($file);
    }
}