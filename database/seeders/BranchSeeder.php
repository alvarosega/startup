<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/branches.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Archivo CSV no encontrado en: $csvPath");
            return;
        }

        $file = fopen($csvPath, 'r');
        fgetcsv($file, 0, ";"); // Omitir cabecera

        DB::transaction(function () use ($file) {
            while (($row = fgetcsv($file, 0, ";")) !== FALSE) {
                if (empty($row[0])) continue;

                // 1. Generación de slug (Lógica original)
                $slug = mb_strtolower(trim($row[0]));
                $slug = str_replace([' ', ' - '], '-', $slug);
                $slug = preg_replace('/[^a-z0-9ñ\-]/u', '', $slug);

                Branch::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name'                        => $row[0],
                        'city'                        => $row[1],
                        'address'                     => $row[2],
                        'phone'                       => $row[3],
                        'latitude'                    => floatval($row[4]),
                        'longitude'                   => floatval($row[5]),
                        'is_default'                  => (bool)$row[6],
                        'delivery_base_fee'           => floatval($row[7]),
                        'delivery_price_per_km'       => floatval($row[8]),
                        'surge_multiplier'            => floatval($row[9]),
                        'min_order_amount'            => floatval($row[10]),
                        'small_order_fee'             => floatval($row[11]),
                        'base_service_fee_percentage' => floatval($row[12]),
                        // Decodificación de campos JSON
                        'coverage_polygon'            => json_decode($row[13], true),
                        'opening_hours'               => json_decode($row[14], true),
                        'is_active'                   => true,
                    ]
                );
            }
        });

        fclose($file);
        $this->command->info('✅ BranchSeeder: Sucursales sincronizadas con éxito.');
    }
}