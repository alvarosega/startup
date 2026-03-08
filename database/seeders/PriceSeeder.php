<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Price, Sku, Branch};
use Illuminate\Support\Facades\DB;

class PriceSeeder extends Seeder {
    // Definición de jerarquía según requerimiento del usuario
    private const PRICE_HIERARCHY = [
        'regular'     => 1,
        'offer'       => 2,
        'member'      => 3,
        'wholesale'   => 4,
        'liquidation' => 5,
        'staff'       => 6,
    ];

    public function run(): void {
        $skus = Sku::whereNotNull('code')->pluck('id', 'code')->toArray();
        $branches = Branch::pluck('id', 'slug')->mapWithKeys(fn($id, $slug) => [strtolower(trim($slug)) => $id])->toArray();

        $file = fopen(database_path('data/prices.csv'), 'r');
        $headers = array_map(fn($h) => strtolower(preg_replace('/^[\xef\xbb\xbf]+/', '', trim((string)$h))), fgetcsv($file, 0, ';'));

        DB::transaction(function () use ($file, $headers, $skus, $branches) {
            $rowNumber = 1;
            while (($data = fgetcsv($file, 0, ';')) !== false) {
                $rowNumber++;
                if (empty(array_filter($data))) continue;

                $row = array_combine($headers, $data);
                $skuId = $skus[$row['sku_code']] ?? null;
                $branchId = $branches[strtolower($row['branch_slug'] ?? '')] ?? null;

                if (!$skuId || !$branchId) {
                    throw new \Exception("ERROR INTEGRIDAD FILA {$rowNumber}: SKU o SUCURSAL no encontrados.");
                }

                $type = strtolower($row['type'] ?? 'regular');
                $priority = self::PRICE_HIERARCHY[$type] ?? 1;

                Price::create([
                    'sku_id'       => $skuId,
                    'branch_id'    => $branchId,
                    'type'         => $type,
                    'list_price'   => $row['list_price'],
                    'final_price'  => $row['final_price'],
                    'min_quantity' => $row['min_quantity'] ?? 1,
                    'priority'     => $priority,
                    'valid_from'   => now(),
                ]);
            }
        });
        fclose($file);
    }
}