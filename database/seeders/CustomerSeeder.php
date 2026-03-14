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
        $branches = Branch::where('is_active', true)->pluck('id')->toArray();
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        DB::transaction(function () use ($faker, $branches, $customerRole) {
            
            for ($i = 0; $i < 10; $i++) {
                $customerId = Str::uuid()->toString();
                $branchId = !empty($branches) ? $faker->randomElement($branches) : null;

                // --- 0. LÓGICA DE GEOPOSICIONAMIENTO PRE-PROCESADA ---
                // Simulamos coordenadas en el eje central de operación
                $lat = -16.5000 + ($faker->randomFloat(8, -0.05, 0.05));
                $lng = -68.1500 + ($faker->randomFloat(8, -0.05, 0.05));

                // 1. Core Customer (Persistencia de Coordenadas)
                DB::table('customers')->insert([
                    'id'           => $customerId,
                    'branch_id'    => $branchId,
                    'phone'        => '+5917' . $faker->randomNumber(7, true),
                    'country_code' => 'BO',
                    'email'        => $faker->unique()->safeEmail(),
                    'password'     => Hash::make('password123'),
                    'trust_score'  => 50,
                    'is_active'    => true,
                    
                    // --- REPLICACIÓN INTEGRAL ---
                    'latitude'     => $lat,
                    'longitude'    => $lng,
                    // ----------------------------

                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // 2. Profile
                DB::table('customer_profiles')->insert([
                    'customer_id'   => $customerId,
                    'first_name'    => $faker->firstName(),
                    'last_name'     => $faker->lastName(),
                    'avatar_type'   => 'icon',
                    'avatar_source' => 'avatar_' . rand(1, 5) . '.svg',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // 3. Address (Sincronizada con el Core)
                if ($branchId) {
                    DB::table('customer_addresses')->insert([
                        'id'          => Str::uuid()->toString(),
                        'customer_id' => $customerId,
                        'branch_id'   => $branchId,
                        'alias'       => 'Casa Principal',
                        'address'     => $faker->streetAddress(),
                        
                        // --- REPLICACIÓN INTEGRAL ---
                        'latitude'    => $lat,
                        'longitude'   => $lng,
                        // ----------------------------

                        'is_default'  => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // 4. Role
                DB::table('model_has_roles')->insert([
                    'role_id'    => $customerRole->id,
                    'model_type' => 'App\Models\Customer',
                    'model_id'   => $customerId,
                ]);
            }
        });

        $this->command->info('✅ 10 Customers Generados con Coordenadas Sincronizadas');
    }
}