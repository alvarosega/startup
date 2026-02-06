<?php

namespace App\Actions\Client;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserAddress;
use App\DTOs\Client\AddressData;
use App\Services\ShopContextService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SaveUserAddress
{
    public function __construct(
        protected ShopContextService $shopContext
    ) {}

    /**
     * Maneja tanto la creación como la edición (re-creación).
     */
    public function execute(User $user, AddressData $data, ?UserAddress $oldAddress = null): UserAddress
    {
        return DB::transaction(function () use ($user, $data, $oldAddress) {
            
            // 1. REGLA DE NEGOCIO: Límite de 3 Direcciones
            // Solo verificamos si estamos creando una nueva (oldAddress es null)
            if (!$oldAddress && $user->addresses()->count() >= 3) {
                throw ValidationException::withMessages([
                    'limit' => 'Has alcanzado el límite de 3 direcciones. Elimina una para continuar.'
                ]);
            }

            // 2. LÓGICA GEOESPACIAL: Buscar cobertura
            // Usamos la función estática de tu modelo Branch
            $coveringBranch = Branch::findCoveringBranch($data->latitude, $data->longitude);

            // 3. PERSISTENCIA
            // Creamos la nueva dirección
            $newAddress = $user->addresses()->create([
                'branch_id'  => $coveringBranch?->id, // Puede ser null si está fuera de zona
                'alias'      => $data->alias,
                'address'    => $data->address,
                'latitude'   => $data->latitude,
                'longitude'  => $data->longitude,
                'reference'  => $data->reference,
                'is_default' => $data->isDefault,
            ]);

            // 4. MANEJO DE EDICIÓN
            // Si estamos editando, borramos la anterior (SoftDelete) para no romper historial de pedidos
            if ($oldAddress) {
                $oldAddress->delete();
            }

            // 5. EFECTO SECUNDARIO: Actualizar Contexto de Tienda
            // Si la dirección tiene cobertura y (es default O es la única), cambiamos la tienda
            $shouldSwitchContext = $newAddress->branch_id && ($data->isDefault || $user->addresses()->count() === 1);

            if ($shouldSwitchContext) {
                $this->updateContext($user, $newAddress);
            }

            return $newAddress;
        });
    }

    protected function updateContext(User $user, UserAddress $address): void
    {
        // Actualizamos la preferencia del usuario en DB
        $user->update(['branch_id' => $address->branch_id]);

        // Actualizamos la sesión actual para que el usuario vea los productos de la nueva zona al instante
        $this->shopContext->setContext($address->branch_id, $address->id);
    }
}