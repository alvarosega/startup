<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;
use Faker\Factory as Faker;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_BO');

        // 1. PROVEEDORES REALES (Tus datos originales)
        $providers = [
            [
                'company_name' => 'Cervecería Boliviana Nacional S.A.',
                'commercial_name' => 'CBN',
                'tax_id' => '1020304050',
                'internal_code' => 'PROV-001',
                // ... resto de tus datos reales ...
                'is_active' => true,
            ],
            // ... (Mantén aquí tus otros 4 proveedores reales originales) ...
        ];

        // 2. PROVEEDORES FICTICIOS (Generación Masiva)
        for ($i = 0; $i < 15; $i++) {
            $company = $faker->company;
            $providers[] = [
                'company_name' => $company . ' S.A.',
                'commercial_name' => explode(' ', $company)[0] . ' Distribución',
                'tax_id' => $faker->unique()->numerify('10########'),
                'internal_code' => 'PROV-' . str_pad($i + 10, 3, '0', STR_PAD_LEFT),
                'contact_name' => $faker->name,
                'email_orders' => $faker->companyEmail,
                'phone' => '7' . $faker->numerify('#######'),
                'address' => $faker->streetAddress,
                'city' => $faker->randomElement(['La Paz', 'Santa Cruz', 'Cochabamba', 'El Alto', 'Tarija']),
                'lead_time_days' => $faker->numberBetween(1, 7),
                'min_order_value' => $faker->randomFloat(2, 100, 2000),
                'credit_days' => $faker->randomElement([0, 7, 15, 30, 45]),
                'credit_limit' => $faker->randomFloat(2, 5000, 50000),
                'is_active' => true,
            ];
        }

        foreach ($providers as $data) {
            Provider::firstOrCreate(
                ['tax_id' => $data['tax_id'] ?? $faker->unique()->numerify('10########')],
                $data
            );
        }
    }
}