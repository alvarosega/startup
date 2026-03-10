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

        // Protegemos la inserción masiva
        DB::transaction(function () use ($faker, $branches, $customerRole) {
            
            for ($i = 0; $i < 10; $i++) {
                $customerId = Str::uuid()->toString();
                $branchId = !empty($branches) ? $faker->randomElement($branches) : null;

                // 1. Core Customer
                DB::table('customers')->insert([
                    'id'           => $customerId,
                    'branch_id'    => $branchId,
                    'phone'        => '+5917' . $faker->randomNumber(7, true), // Formato Bolivia
                    'country_code' => 'BO',
                    'email'        => $faker->unique()->safeEmail(),
                    'password'     => Hash::make('password123'),
                    'trust_score'  => 50,
                    'is_active'    => true,
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

                // 3. Address (Simulando coordenadas céntricas)
                if ($branchId) {
                    DB::table('customer_addresses')->insert([
                        'id'          => Str::uuid()->toString(),
                        'customer_id' => $customerId,
                        'branch_id'   => $branchId,
                        'alias'       => 'Casa Principal',
                        'address'     => $faker->streetAddress(),
                        'latitude'    => -16.5000 + ($faker->randomFloat(4, -0.05, 0.05)),
                        'longitude'   => -68.1500 + ($faker->randomFloat(4, -0.05, 0.05)),
                        'is_default'  => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // 4. Role (Spatie Model Has Roles usa el modelo real para polimorfismo)
                DB::table('model_has_roles')->insert([
                    'role_id'    => $customerRole->id,
                    'model_type' => 'App\Models\Customer',
                    'model_id'   => $customerId,
                ]);
            }
        });

        $this->command->info('✅ 10 Customers Generados (Silo Customer Blindado)');
    }
}