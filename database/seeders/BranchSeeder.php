<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Sede Central - La Paz (Sopocachi)',
                'city' => 'La Paz',
                'address' => 'Av. 6 de Agosto #2020',
                'phone' => '22445566',
                'latitude' => -16.508356,
                'longitude' => -68.126282,
                // Polígono: Un cuadrado aprox de 1km alrededor del punto
                'coverage_polygon' => [
                    ['lat' => -16.500000, 'lng' => -68.135000], // Noroeste
                    ['lat' => -16.500000, 'lng' => -68.115000], // Noreste
                    ['lat' => -16.520000, 'lng' => -68.115000], // Sureste
                    ['lat' => -16.520000, 'lng' => -68.135000], // Suroeste
                ],
            ],
            [
                'name' => 'Sucursal Sur - Calacoto',
                'city' => 'La Paz',
                'address' => 'Av. Ballivián #1500',
                'phone' => '22778899',
                'latitude' => -16.540602,
                'longitude' => -68.086776,
                'coverage_polygon' => [
                    ['lat' => -16.530000, 'lng' => -68.095000],
                    ['lat' => -16.530000, 'lng' => -68.075000],
                    ['lat' => -16.550000, 'lng' => -68.075000],
                    ['lat' => -16.550000, 'lng' => -68.095000],
                ],
            ],
            [
                'name' => 'Sucursal El Alto - Satélite',
                'city' => 'El Alto',
                'address' => 'Av. Satélite #500',
                'phone' => '22881122',
                'latitude' => -16.510850,
                'longitude' => -68.169800,
                'coverage_polygon' => [
                    ['lat' => -16.500000, 'lng' => -68.180000],
                    ['lat' => -16.500000, 'lng' => -68.160000],
                    ['lat' => -16.520000, 'lng' => -68.160000],
                    ['lat' => -16.520000, 'lng' => -68.180000],
                ],
            ],
            [
                'name' => 'Sucursal Santa Cruz - Equipetrol',
                'city' => 'Santa Cruz',
                'address' => 'Av. San Martín #300',
                'phone' => '33445566',
                'latitude' => -17.769800,
                'longitude' => -63.195000,
                'coverage_polygon' => [
                    ['lat' => -17.750000, 'lng' => -63.210000],
                    ['lat' => -17.750000, 'lng' => -63.180000],
                    ['lat' => -17.790000, 'lng' => -63.180000],
                    ['lat' => -17.790000, 'lng' => -63.210000],
                ],
            ],
            [
                'name' => 'Sucursal Cochabamba - Centro',
                'city' => 'Cochabamba',
                'address' => 'Av. Ayacucho #400',
                'phone' => '44223311',
                'latitude' => -17.393800,
                'longitude' => -66.157100,
                'coverage_polygon' => [
                    ['lat' => -17.380000, 'lng' => -66.170000],
                    ['lat' => -17.380000, 'lng' => -66.140000],
                    ['lat' => -17.410000, 'lng' => -66.140000],
                    ['lat' => -17.410000, 'lng' => -66.170000],
                ],
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['name' => $branch['name']], // Evita duplicados si se corre el seed de nuevo
                [
                    'city' => $branch['city'],
                    'address' => $branch['address'],
                    'phone' => $branch['phone'],
                    'latitude' => $branch['latitude'],
                    'longitude' => $branch['longitude'],
                    'coverage_polygon' => $branch['coverage_polygon'], // Laravel castea esto a JSON automáticamente si está definido en el modelo
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