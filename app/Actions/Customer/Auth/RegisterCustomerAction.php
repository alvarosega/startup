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

            // 2. Procesar Avatar
            $avatarSource = 'avatar_1.svg';
            
            if ($data->avatar_type === 'custom' && $data->avatar_file) {
                // Usamos ID hex para la carpeta
                $folderId = bin2hex($customer->getRawOriginal('id'));
                $avatarSource = $data->avatar_file->store("customers/{$folderId}/avatars", 'public');
            } elseif ($data->avatar_type === 'icon' && $data->avatar_source) {
                $avatarSource = $data->avatar_source;
            }

            // 3. Crear Perfil
            $customer->profile()->create([
                'first_name' => 'Usuario', // O el nombre si lo pedimos
                'last_name' => '',
                'avatar_type' => $data->avatar_type,
                'avatar_source' => $avatarSource,
            ]);

            // 4. Crear Dirección (CONVERSIÓN CRÍTICA DE BINARIO)
            $binaryBranchId = null;
            if ($data->branch_id) {
                // Si es un Hex válido de 32 caracteres, lo convertimos a BINARIO de 16 bytes
                if (ctype_xdigit($data->branch_id) && strlen($data->branch_id) === 32) {
                    $binaryBranchId = hex2bin($data->branch_id);
                } else {
                    $binaryBranchId = $data->branch_id; // Fallback
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