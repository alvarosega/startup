<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use Faker\Factory as Faker;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_BO');

        // 1. SUCURSALES REALES (Golden Data)
        $branches = [
            [
                'name' => 'Sede Central - La Paz (Sopocachi)',
                'city' => 'La Paz',
                'address' => 'Av. 6 de Agosto #2020',
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
            // ... (Mantén aquí el resto de tus sucursales manuales originales) ...
        ];

        // 2. SUCURSALES GENERADAS (Masivas)
        // Definimos centros de ciudades para generar coordenadas cercanas
        $cityCenters = [
            'La Paz' => ['lat' => -16.4897, 'lng' => -68.1193],
            'Santa Cruz' => ['lat' => -17.7833, 'lng' => -63.1821],
            'Cochabamba' => ['lat' => -17.3935, 'lng' => -66.1570],
            'El Alto' => ['lat' => -16.5000, 'lng' => -68.1500],
            'Tarija' => ['lat' => -21.5355, 'lng' => -64.7296],
            'Sucre' => ['lat' => -19.0353, 'lng' => -65.2592],
            'Oruro' => ['lat' => -17.9666, 'lng' => -67.1166],
        ];

        for ($i = 0; $i < 45; $i++) {
            $city = $faker->randomElement(array_keys($cityCenters));
            $center = $cityCenters[$city];

            // Generar lat/lng aleatoria cerca del centro (aprox +/- 2km)
            $lat = $center['lat'] + $faker->randomFloat(6, -0.02, 0.02);
            $lng = $center['lng'] + $faker->randomFloat(6, -0.02, 0.02);

            // Generar un polígono simple (cuadrado) alrededor del punto
            $offset = 0.005; // Aprox 500m
            $polygon = [
                ['lat' => $lat + $offset, 'lng' => $lng - $offset], // NW
                ['lat' => $lat + $offset, 'lng' => $lng + $offset], // NE
                ['lat' => $lat - $offset, 'lng' => $lng + $offset], // SE
                ['lat' => $lat - $offset, 'lng' => $lng - $offset], // SW
            ];

            $branches[] = [
                'name' => "Sucursal $city - " . $faker->streetName,
                'city' => $city,
                'address' => $faker->address,
                'phone' => '4' . $faker->numerify('#######'), // Fijos BO
                'latitude' => $lat,
                'longitude' => $lng,
                'coverage_polygon' => $polygon,
            ];
        }

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