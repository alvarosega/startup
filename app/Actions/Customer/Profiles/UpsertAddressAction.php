<?php

declare(strict_types=1);

namespace App\Actions\Customer\Profiles;

use App\Models\{Customer, CustomerAddress};
use App\DTOs\Customer\Profiles\AddressData;
use App\Services\Geo\BranchCoverageService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertAddressAction
{
    public function __construct(
        protected readonly BranchCoverageService $geoService,
        protected readonly ShopContextService $shopContext
    ) {}

    /**
     * Ejecuta la inserción o actualización atómica de una dirección de entrega.
     */
    public function execute(Customer $customer, AddressData $data, ?string $addressId = null): CustomerAddress
    {
        return DB::transaction(function () use ($customer, $data, $addressId) {
            // Bloqueo pesimista para evitar condiciones de carrera en ráfagas de red
            $customer->lockForUpdate()->find($customer->id);

            // El conteo ignora automáticamente las direcciones con Soft Delete
            if (!$addressId && $customer->addresses()->count() >= 10) {
                throw ValidationException::withMessages([
                    'limit' => 'Se ha alcanzado el límite máximo de 10 ubicaciones permitidas en su cuenta.'
                ]);
            }

            // LEY: Si está fuera de cobertura devuelve NULL. Se elimina el fallback destructivo.
            $identifiedBranchId = $this->geoService->identifyBranch($data->latitude, $data->longitude);

            $shouldBeDefault = $customer->addresses()->count() === 0 || $data->isDefault;

            if ($shouldBeDefault) {
                $customer->addresses()->update(['is_default' => false]);
                // Obligatorio: Purga de sesión para forzar la actualización del layout e inventarios
                session()->forget(['user_addr_alias', 'shop_branch_id']);
            }

            /** @var CustomerAddress $address */
            $address = $customer->addresses()->updateOrCreate(
                ['id' => $addressId],
                [
                    'alias'      => $data->alias,
                    'address'    => $data->address,
                    'reference'  => $data->details,
                    'latitude'   => $data->latitude,
                    'longitude'  => $data->longitude,
                    'branch_id'  => $identifiedBranchId, // UUID o NULL
                    'is_default' => $shouldBeDefault
                ]
            );

            // Replicación de estado logístico al perfil troncal del cliente
            if ($shouldBeDefault) {
                $customer->update([
                    'branch_id' => $identifiedBranchId, // Sincroniza NULL si está fuera de zona
                    'latitude'  => $data->latitude,
                    'longitude' => $data->longitude,
                ]);
            }

            return $address;
        });
    }
}