<?php

namespace App\Actions\Admin\User;

use App\Models\Customer;
use Spatie\Permission\Models\Role;
use App\DTOs\Admin\User\StoreUserDTO;
use Illuminate\Support\Facades\DB;

class StoreUserAction
{
    public function execute(StoreUserDTO $data): void
    {
        DB::transaction(function () use ($data) {
            // 1. Crear Credenciales (Tabla customers)
            $customer = Customer::create([
                'email'        => $data->email,
                'phone'        => $data->phone,
                'password'     => $data->password, // Modelo maneja el Hash Cast
                'branch_id'    => $data->branchId,
                'is_active'    => true,
                'country_code' => 'BO'
            ]);

            // 2. Crear Perfil (Tabla customer_profiles)
            $customer->profile()->create([
                'first_name' => $data->firstName,
                'last_name'  => $data->lastName,
            ]);

            // 3. Crear Punto Logístico (Tabla customer_addresses)
            $customer->addresses()->create([
                'alias'      => 'Punto de Registro',
                'address'    => $data->address,
                'latitude'   => $data->latitude,
                'longitude'  => $data->longitude,
                'branch_id'  => $data->branchId,
                'is_default' => true,
            ]);

            // 4. Asignación de Identidad Spatie
            $role = Role::where('name', 'customer')->firstOrFail();
            $customer->assignRole($role);
        });
    }
}