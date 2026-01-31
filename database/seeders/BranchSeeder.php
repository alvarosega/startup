<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla para evitar duplicados si se corre varias veces
        // Branch::truncate(); // Descomentar si es necesario limpiar

        $branches = [
            // 1. LA PAZ (Sopocachi)
            [
                'name' => 'Sede Central - La Paz',
                'city' => 'La Paz',
                'address' => 'Av. 6 de Agosto #2020, Sopocachi',
                'phone' => '22445566',
                'latitude' => -16.508356,
                'longitude' => -68.126282,
                'coverage_polygon' => [
                    ['lat' => -16.500000, 'lng' => -68.135000], 
                    ['lat' => -16.500000, 'lng' => -68.115000],
                    ['lat' => -16.520000, 'lng' => -68.115000], 
                    ['lat' => -16.520000, 'lng' => -68.135000],
                ],
            ],
            // 2. SANTA CRUZ (Equipetrol)
            [
                'name' => 'Sede Santa Cruz - Equipetrol',
                'city' => 'Santa Cruz',
                'address' => 'Av. San Martín, Calle 5 Este',
                'phone' => '33445566',
                'latitude' => -17.769000,
                'longitude' => -63.195000,
                'coverage_polygon' => [
                    ['lat' => -17.760000, 'lng' => -63.200000],
                    ['lat' => -17.760000, 'lng' => -63.190000],
                    ['lat' => -17.780000, 'lng' => -63.190000],
                    ['lat' => -17.780000, 'lng' => -63.200000],
                ],
            ],
            // 3. COCHABAMBA (Cala Cala)
            [
                'name' => 'Sede Cochabamba - Cala Cala',
                'city' => 'Cochabamba',
                'address' => 'Av. Libertador Bolívar #100',
                'phone' => '44445566',
                'latitude' => -17.370000,
                'longitude' => -66.160000,
                'coverage_polygon' => [
                    ['lat' => -17.360000, 'lng' => -66.170000],
                    ['lat' => -17.360000, 'lng' => -66.150000],
                    ['lat' => -17.380000, 'lng' => -66.150000],
                    ['lat' => -17.380000, 'lng' => -66.170000],
                ],
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['name' => $branch['name']], 
                [
                    'city' => $branch['city'],
                    'address' => $branch['address'],
                    'phone' => $branch['phone'],
                    'latitude' => $branch['latitude'],
                    'longitude' => $branch['longitude'],
                    'coverage_polygon' => $branch['coverage_polygon'],
                    'is_active' => true,
                    'opening_hours' => [
                        'L-V' => '08:00 - 18:00',
                        'S' => '09:00 - 13:00'
                    ]
                ]
            );
        }
    }
}