<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    /**
     * Alterna la dirección principal de un cliente y sincroniza el contexto de la aplicación.
     */
    public function execute(Customer $customer, string $addressId): void
    {
        DB::transaction(function () use ($customer, $addressId) {
            // Bloqueo de seguridad para resguardar la consistencia de la fila
            $address = $customer->addresses()->where('id', $addressId)->lockForUpdate()->firstOrFail();

            // 1. Resetear estados previos
            $customer->addresses()->update(['is_default' => false]);

            // 2. Establecer nuevo nodo predeterminado
            $address->update(['is_default' => true]);

            // 3. Replicar de forma idéntica al perfil del usuario (Hereda el branch_id o su nulabilidad)
            $customer->update([
                'branch_id' => $address->branch_id,
                'latitude'  => $address->latitude,
                'longitude' => $address->longitude,
            ]);

            // 4. Invalidación absoluta de variables de control de la sesión cliente
            session()->forget(['user_addr_alias', 'shop_branch_id']);
        });
    }
}