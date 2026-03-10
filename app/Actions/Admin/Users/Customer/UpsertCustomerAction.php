<?php

namespace App\Actions\Admin\Users\Customer;

use App\Models\Customer;
use Spatie\Permission\Models\Role;
use App\DTOs\Admin\Users\Customer\UpsertCustomerDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UpsertCustomerAction
{
    public function execute(UpsertCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($dto) {
            $isNew = empty($dto->id);
            $customerId = $dto->id ?? Str::uuid()->toString();

            // 1. Data Core (Customers)
            $customerData = [
                'email'        => $dto->email,
                'phone'        => $dto->phone,
                'branch_id'    => $dto->branchId,
                'is_active'    => $dto->isActive,
            ];

            // Si es nuevo o enviaron password en la edición
            if ($isNew && empty($customerData['country_code'])) {
                $customerData['country_code'] = 'BO'; // Default system
            }
            if ($dto->password) {
                $customerData['password'] = Hash::make($dto->password);
            }

            $customer = Customer::updateOrCreate(['id' => $customerId], $customerData);

            // 2. Data Perfil (Customer_Profiles)
            $customer->profile()->updateOrCreate(
                ['customer_id' => $customer->id],
                [
                    'first_name' => $dto->firstName,
                    'last_name'  => $dto->lastName,
                ]
            );

            // 3. Data Logística y Permisos (SOLO EN CREACIÓN)
            if ($isNew && $dto->latitude && $dto->longitude && $dto->address) {
                $customer->addresses()->create([
                    'alias'      => 'Punto de Registro',
                    'address'    => $dto->address,
                    'latitude'   => $dto->latitude,
                    'longitude'  => $dto->longitude,
                    'branch_id'  => $dto->branchId,
                    'is_default' => true,
                ]);

                // Asignación de Rol
                $role = Role::where('name', 'customer')->first();
                if ($role) {
                    $customer->assignRole($role);
                }
            }

            // 4. INVALIDACIÓN DE CACHÉ (Regla 3.B)
            Cache::forget('admin_customers_list_base');

            return $customer;
        });
    }
}