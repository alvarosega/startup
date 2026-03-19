<?php

namespace App\Actions\Customer\Profiles;

use App\Models\{Customer, CustomerAddress};
use App\DTOs\Customer\Profiles\AddressData;
use App\Services\Geo\BranchCoverageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertAddressAction
{
    public function __construct(
        protected BranchCoverageService $geoService,
        protected \App\Services\ShopContextService $shopContext // <--- AÑADIR
    ) {}

    public function execute(Customer $customer, AddressData $data, ?string $addressId = null): CustomerAddress
    {
        // 1. LEY: Límite ampliado a 10 ubicaciones
        if (!$addressId && $customer->addresses()->count() >= 10) {
            throw ValidationException::withMessages(['limit' => 'Has alcanzado el máximo de 10 ubicaciones permitidas.']);
        }

        return DB::transaction(function () use ($customer, $data, $addressId) {
            $shouldBeDefault = $customer->addresses()->count() === 0 || $data->isDefault;
    
            // 1. Identificación con FALLBACK (Ley de No-Nulidad)
            $identifiedBranchId = $this->geoService->identifyBranch($data->latitude, $data->longitude) 
                                  ?? $this->shopContext->getDefaultBranchId();
    
            if ($shouldBeDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }
    
            // 2. Persistencia
            $address = $customer->addresses()->updateOrCreate(
                ['id' => $addressId],
                [
                    'alias'      => $data->alias,
                    'address'    => $data->address,
                    'reference'  => $data->details,
                    'latitude'   => $data->latitude,
                    'longitude'  => $data->longitude,
                    'branch_id'  => $identifiedBranchId, // Siempre tendrá un UUID válido
                    'is_default' => $shouldBeDefault
                ]
            );
    
            // 3. Sincronización del Núcleo
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