<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Customer, CustomerProfile, Branch};

class TestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpieza de duplicados
        Customer::where('phone', '+59170000000')->forceDelete();

        // 2. Garantizar una sucursal (Indispensable por el NOT NULL)
        $branch = Branch::where('is_default', true)->first() 
                  ?? Branch::first() 
                  ?? Branch::create([
                      'id' => \Illuminate\Support\Str::uuid(),
                      'name' => 'Sucursal Central Test',
                      'slug' => 'sucursal-central-test',
                      'is_default' => true,
                      'is_active' => true
                  ]);

        // 3. Crear Cliente con branch_id y coordenadas
        $customer = Customer::create([
            'phone'        => '+59170000000',
            'email'        => 'test@test.com',
            'country_code' => 'BO',
            'password'     => 'password', // Hashed por el cast del modelo
            'branch_id'    => $branch->id, // <--- CORRECCIÓN CRÍTICA
            'latitude'     => -16.5000,
            'longitude'    => -68.1500,
            'is_active'    => true,
        ]);

        // 4. Crear Perfil
        CustomerProfile::create([
            'customer_id'   => $customer->id,
            'first_name'    => 'Test',
            'last_name'     => 'User',
            'avatar_type'   => 'icon',
            'avatar_source' => 'avatar_1.svg',
        ]);

        // 5. Crear Dirección (También requiere branch_id)
        $customer->addresses()->create([
            'alias'      => 'Casa',
            'address'    => 'Calle Falsa 123',
            'latitude'   => -16.5000,
            'longitude'  => -68.1500,
            'branch_id'  => $branch->id, // <--- CORRECCIÓN CRÍTICA
            'is_default' => true,
        ]);

        $this->command->info("Test User: +59170000000 | Sucursal: {$branch->name}");
    }
}