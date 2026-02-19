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
                'is_default' => true, // Esta será el fallback del sistema
                'coverage_polygon' => [
                    [-16.500000, -68.135000], 
                    [-16.500000, -68.115000],
                    [-16.520000, -68.115000], 
                    [-16.520000, -68.135000],
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
                'coverage_polygon' => [
                    [-17.760000, -63.200000],
                    [-17.760000, -63.190000],
                    [-17.780000, -63.190000],
                    [-17.780000, -63.200000],
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
                'coverage_polygon' => [
                    [-17.360000, -66.170000],
                    [-17.360000, -66.150000],
                    [-17.380000, -66.150000],
                    [-17.380000, -66.170000],
                ],
            ],
        ];

        foreach ($branches as $branch) {
            // Usamos transaction para asegurar que el HasUuids genere el ID correctamente
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