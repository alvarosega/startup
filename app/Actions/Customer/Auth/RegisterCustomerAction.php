<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Geo\BranchCoverageService;
use App\Actions\Customer\Cart\SyncGuestCartAction;
use App\DTOs\Customer\Cart\SyncCartDTO;
use App\Services\ShopContextService;

class RegisterCustomerAction
{
    public function __construct(
        protected BranchCoverageService $geoService,
        protected SyncGuestCartAction $syncAction,
        protected ShopContextService $shopContext
    ) {}

    public function execute(RegisterCustomerData $data): Customer
    {
        $identifiedBranch = $this->geoService->identifyBranch($data->latitude, $data->longitude);
        $assignedBranchId = $identifiedBranch ?? $this->shopContext->getDefaultBranchId();
    
        // 1. Creación Atómica del Usuario y su Contexto
        $customer = DB::transaction(function () use ($data, $assignedBranchId) {
            $user = Customer::create([
                'phone'        => $data->phone,
                'email'        => $data->email,
                'password'     => Hash::make($data->password),
                'country_code' => strtoupper($data->countryCode),
                'branch_id'    => $assignedBranchId,
                'is_active'    => true,
                'latitude'     => $data->latitude,
                'longitude'    => $data->longitude,
            ]);
    
            $user->profile()->create([
                'first_name'    => $data->firstName,
                'last_name'     => $data->lastName,
                'avatar_type'   => $data->avatarType,
                'avatar_source' => $data->avatarSource ?? 'avatar_1.svg',
            ]);
    
            $user->addresses()->create([
                'alias'      => $data->alias ?? 'Mi Ubicación',
                'address'    => $data->address,
                'reference'  => $data->details,
                'latitude'   => $data->latitude,
                'longitude'  => $data->longitude,
                'branch_id'  => $assignedBranchId,
                'is_default' => true,
            ]);

            $user->assignRole('customer');
            
            return $user;
        });

        // 2. Sincronización del Carrito (FUERA de la transacción principal)
        // Si esto falla, el usuario ya existe y puede loguearse igual.
        if ($data->guestUuid) {
            try {
                $this->syncAction->execute(new SyncCartDTO(
                    customerId: $customer->id,
                    guestUuid:  $data->guestUuid,
                    branchId:   $assignedBranchId
                ));
            } catch (\Exception $e) {
                Log::error('[RegisterSyncCart] Fallo al sincronizar carrito post-registro', ['error' => $e->getMessage()]);
                // No lanzamos la excepción. Permitimos que el flujo continúe.
            }
        }

        return $customer;
    }
}