<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Sede Central - La Paz',
                'city' => 'La Paz',
                'address' => 'Av. 6 de Agosto #2020, Sopocachi',
                'phone' => '22445566',
                'latitude' => -16.508356,
                'longitude' => -68.126282,
                'is_default' => true,
                // Topografía compleja (La Paz): Envíos más caros
                'delivery_base_fee' => 7.00,
                'delivery_price_per_km' => 2.50,
                'surge_multiplier' => 1.00,
                'min_order_amount' => 45.00,
                'small_order_fee' => 6.00,
                'base_service_fee_percentage' => 5.00,
                // Polígono ampliado (aprox 6km a la redonda desde el centro)
                'coverage_polygon' => [
                    [-16.450000, -68.170000], 
                    [-16.450000, -68.080000],
                    [-16.560000, -68.080000], 
                    [-16.560000, -68.170000],
                ],
            ],
            [
                'name' => 'Sede Santa Cruz - Equipetrol',
                'city' => 'Santa Cruz',
                'address' => 'Av. San Martín, Calle 5 Este',
                'phone' => '33445566',
                'latitude' => -17.769000,
                'longitude' => -63.195000,
                'is_default' => false,
                // Topografía plana (Santa Cruz): Envíos ligeramente más baratos por km
                'delivery_base_fee' => 5.00,
                'delivery_price_per_km' => 1.80,
                'surge_multiplier' => 1.00,
                'min_order_amount' => 40.00,
                'small_order_fee' => 5.00,
                'base_service_fee_percentage' => 5.00,
                // Polígono ampliado
                'coverage_polygon' => [
                    [-17.700000, -63.250000],
                    [-17.700000, -63.130000],
                    [-17.840000, -63.130000],
                    [-17.840000, -63.250000],
                ],
            ],
            [
                'name' => 'Sede Cochabamba - Cala Cala',
                'city' => 'Cochabamba',
                'address' => 'Av. Libertador Bolívar #100',
                'phone' => '44445566',
                'latitude' => -17.370000,
                'longitude' => -66.160000,
                'is_default' => false,
                'delivery_base_fee' => 6.00,
                'delivery_price_per_km' => 2.00,
                'surge_multiplier' => 1.00,
                'min_order_amount' => 35.00,
                'small_order_fee' => 5.00,
                'base_service_fee_percentage' => 5.00,
                // Polígono ampliado
                'coverage_polygon' => [
                    [-17.310000, -66.220000],
                    [-17.310000, -66.100000],
                    [-17.430000, -66.100000],
                    [-17.430000, -66.220000],
                ],
            ],
        ];

        foreach ($branches as $branch) {
            DB::transaction(function () use ($branch) {
                Branch::updateOrCreate(
                    ['name' => $branch['name']], 
                    [
                        'city' => $branch['city'],
                        'address' => $branch['address'],
                        'phone' => $branch['phone'],
                        'latitude' => $branch['latitude'],
                        'longitude' => $branch['longitude'],
                        'coverage_polygon' => $branch['coverage_polygon'],
                        'is_default' => $branch['is_default'],
                        'is_active' => true,
                        // Logsítica
                        'delivery_base_fee' => $branch['delivery_base_fee'],
                        'delivery_price_per_km' => $branch['delivery_price_per_km'],
                        'surge_multiplier' => $branch['surge_multiplier'],
                        'min_order_amount' => $branch['min_order_amount'],
                        'small_order_fee' => $branch['small_order_fee'],
                        'base_service_fee_percentage' => $branch['base_service_fee_percentage'],
                        'opening_hours' => [
                            ['day' => 'L-V', 'range' => '08:00 - 18:00'],
                            ['day' => 'S', 'range' => '09:00 - 13:00']
                        ]
                    ]
                );
            });
        }
    }
}