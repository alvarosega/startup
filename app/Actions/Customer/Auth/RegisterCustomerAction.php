<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterCustomerAction
{
    public function execute(RegisterCustomerData $data): Customer
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear Cliente
            $customer = Customer::create([
                'phone' => $data->phone,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'is_active' => true,
                'country_code' => 'BO'
            ]);
            // Dentro del DB::transaction
            $customer->assignRole('customer');
            // 2. Procesar Avatar
            $avatarSource = 'avatar_1.svg';

            if ($data->avatarType === 'custom' && $data->avatarFile) {
                // Usamos ID hex para la carpeta
                $folderId = bin2hex($customer->getRawOriginal('id'));
                $avatarSource = $data->avatarFile->store("customers/{$folderId}/avatars", 'public');
            } elseif ($data->avatarType === 'icon' && $data->avatarSource) {
                $avatarSource = $data->avatarSource;
            }
            $customer->profile()->create([
                'first_name'    => $data->firstName,
                'last_name'     => $data->lastName,
                'latitude'      => $data->latitude,
                'longitude'     => $data->longitude,
                'address'       => $data->address,
                'avatar_type'   => $data->avatarType,
                'avatar_source' => $avatarSource,
            ]);
            // 4. Crear Dirección (CONVERSIÓN CRÍTICA DE BINARIO)
            $binaryBranchId = null;
            if ($data->branchId) {
                // Si es un Hex válido de 32 caracteres, lo convertimos a BINARIO de 16 bytes
                if (ctype_xdigit($data->branchId) && strlen($data->branchId) === 32) {
                    $binaryBranchId = hex2bin($data->branchId);
                } else {
                    $binaryBranchId = $data->branchId; // Fallback
                }
            }

            $customer->addresses()->create([
                'alias' => $data->alias,
                'address' => $data->address,
                'reference' => $data->details,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'branch_id' => $binaryBranchId, // Insertamos BINARIO
                'is_default' => true,
            ]);

            Log::info("Cliente registrado exitosamente: {$customer->id}");

            return $customer;
        });
    }
}