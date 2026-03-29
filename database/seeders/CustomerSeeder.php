<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Branch;
use Faker\Factory as Faker;
use Spatie\Permission\Models\Role;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        // Recuperamos sucursales activas con sus polígonos
        $branches = Branch::where('is_active', true)->get(['id']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        DB::transaction(function () use ($faker, $branches, $customerRole) {
            
            for ($i = 0; $i < 10; $i++) {
                $customerId = Str::uuid()->toString();
                $branch = $branches->isNotEmpty() ? $branches->random() : null;
                $branchId = $branch ? $branch->id : null;

                // --- 0. GEOPOSICIONAMIENTO SIMULADO (Eje La Paz, Bolivia) ---
                $lat = -16.5000 + ($faker->randomFloat(8, -0.04, 0.04));
                $lng = -68.1500 + ($faker->randomFloat(8, -0.04, 0.04));

                // 1. Core Customer (Persistencia de Identidad)
                DB::table('customers')->insert([
                    'id'           => $customerId,
                    'branch_id'    => $branchId,
                    'phone'        => '+591' . $faker->numberBetween(60000000, 79999999),
                    'country_code' => 'BO',
                    'email'        => $faker->unique()->safeEmail(),
                    'password'     => Hash::make('password'), // Clave estándar de desarrollo
                    'trust_score'  => rand(40, 95),
                    'is_active'    => true,
                    'latitude'     => $lat,
                    'longitude'    => $lng,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // 2. Profile (Sincronizado con availableAvatars 1-8 .png)
                DB::table('customer_profiles')->insert([
                    'customer_id'   => $customerId,
                    'first_name'    => strtoupper($faker->firstName()),
                    'last_name'     => strtoupper($faker->lastName()),
                    'avatar_type'   => 'icon',
                    'avatar_source' => 'avatar_' . rand(1, 8) . '.png', // Sincronizado con Assets
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 3. Address (Punto de Entrega Logístico)
                if ($branchId) {
                    DB::table('customer_addresses')->insert([
                        'id'          => Str::uuid()->toString(),
                        'customer_id' => $customerId,
                        'branch_id'   => $branchId,
                        'alias'       => $faker->randomElement(['CASA', 'TRABAJO', 'OFICINA', 'DEPÓSITO']),
                        'address'     => strtoupper($faker->streetAddress()),
                        'reference'   => strtoupper($faker->secondaryAddress() . ' - PORTÓN ' . $faker->colorName()), // El "details" del form
                        'latitude'    => $lat,
                        'longitude'   => $lng,
                        'is_default'  => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // 4. Role (Spatie)
                DB::table('model_has_roles')->insert([
                    'role_id'    => $customerRole->id,
                    'model_type' => 'App\Models\Customer',
                    'model_id'   => $customerId,
                ]);
            }
        });

        $this->command->info('✅ PROTOCOLO COMPLETADO: 10 Clientes inyectados con éxito.');
    }
}