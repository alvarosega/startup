<?php

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
        protected BranchCoverageService $geoService,
        protected ShopContextService $shopContext
    ) {}

    public function execute(Customer $customer, AddressData $data, ?string $addressId = null): CustomerAddress
    {
        return DB::transaction(function () use ($customer, $data, $addressId) {
            // Bloqueo pesimista para evitar que ráfagas de red superen el límite de 10
            $customer->lockForUpdate()->find($customer->id);

            if (!$addressId && $customer->addresses()->count() >= 10) {
                throw ValidationException::withMessages([
                    'limit' => 'Has alcanzado el máximo de 10 ubicaciones permitidas.'
                ]);
            }

            // Resolución de sucursal con el service de polígonos
            $identifiedBranchId = $this->geoService->identifyBranch($data->latitude, $data->longitude) 
                                  ?? $this->shopContext->getDefaultBranchId();

            $shouldBeDefault = $customer->addresses()->count() === 0 || $data->isDefault;

            if ($shouldBeDefault) {
                $customer->addresses()->update(['is_default' => false]);
                // Obligatorio: Limpiar caché de sesión para el Header
                session()->forget('user_addr_alias');
            }

            $address = $customer->addresses()->updateOrCreate(
                ['id' => $addressId],
                [
                    'alias'      => $data->alias,
                    'address'    => $data->address,
                    'reference'  => $data->details,
                    'latitude'   => $data->latitude,
                    'longitude'  => $data->longitude,
                    'branch_id'  => $identifiedBranchId,
                    'is_default' => $shouldBeDefault
                ]
            );

            // Replicación al núcleo para cálculos de stock/precio inmediatos
            if ($shouldBeDefault) {
                $customer->update([
                    'branch_id' => $identifiedBranchId,
                    'latitude'  => $data->latitude,
                    'longitude' => $data->longitude,
                ]);
            }

            return $address;
        });
    }
}