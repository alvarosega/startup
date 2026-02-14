<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerProfile;

class TestCustomerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Borrar usuario previo para evitar error de Unique
        // Borramos buscando ambas versiones por si acaso
        Customer::where('phone', '+59170000000')->forceDelete();
        Customer::where('phone', '70000000')->forceDelete();

        // 2. Crear Cliente (Tabla Login)
        $customer = Customer::create([
            'phone' => '+59170000000', // <--- FORMATO CORRECTO CON CÓDIGO PAÍS
            'email' => 'test@test.com',
            'country_code' => 'BO',
            
            // CONTRASEÑA PLANA (Tu modelo Customer tiene el cast 'hashed', así que esto se encripta solo)
            'password' => 'password', 
            
            'is_active' => true,
        ]);

        // 3. Crear Perfil (Tabla Datos Personales)
        CustomerProfile::create([
            'customer_id' => $customer->id, // El Trait maneja el ID binario
            'first_name' => 'Test',
            'last_name' => 'User',
            'avatar_type' => 'icon',
            'avatar_source' => 'avatar_1.svg',
        ]);

        $this->command->info('Usuario Test creado:');
        $this->command->info('Phone: +59170000000');
        $this->command->info('Pass:  password');
    }
}