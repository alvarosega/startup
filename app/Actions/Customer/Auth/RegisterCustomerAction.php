<?php

namespace App\Actions\Customer\Auth;

use App\Models\Customer;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // <--- CORRECCIÓN: Falta esta línea
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
        // 1. Resolución de sucursal (Garantiza que NUNCA sea null antes del insert)
        $identifiedBranch = $this->geoService->identifyBranch($data->latitude, $data->longitude);
        $assignedBranchId = $identifiedBranch ?? $this->shopContext->getDefaultBranchId();

        $customer = DB::transaction(function () use ($data, $assignedBranchId) {
            // 2. Creación del Customer (branch_id es obligatorio ahora)
            $user = Customer::create([
                'phone'        => $data->phone,
                'email'        => $data->email,
                'password'     => Hash::make($data->password),
                'country_code' => strtoupper($data->countryCode),
                'branch_id'    => $assignedBranchId, // <--- Obligatorio
                'latitude'     => $data->latitude,
                'longitude'    => $data->longitude,
                'is_active'    => true,
            ]);

            $user->profile()->create([
                'first_name'    => $data->firstName,
                'last_name'     => $data->lastName,
                'avatar_type'   => $data->avatarType,
                'avatar_source' => $data->avatarSource ?? 'avatar_1.svg',
            ]);

            // 3. Creación de dirección (branch_id también es obligatorio aquí)
            $user->addresses()->create([
                'alias'      => $data->alias ?? 'Mi Ubicación',
                'address'    => $data->address,
                'reference'  => $data->details,
                'latitude'   => $data->latitude,
                'longitude'  => $data->longitude,
                'branch_id'  => $assignedBranchId, // <--- Obligatorio
                'is_default' => true,
            ]);

            $user->assignRole('customer');
            return $user;
        });

        // 4. Autenticación fuera de la transacción para evitar estados inconsistentes
        Auth::guard('customer')->login($customer);

        // 5. Sincronización
        if ($data->guestUuid) {
            try {
                $this->syncAction->execute(new SyncCartDTO(
                    customerId: $customer->id,
                    guestUuid:  $data->guestUuid,
                    branchId:   $assignedBranchId
                ));
            } catch (\Exception $e) {
                Log::error('[RegisterSyncCart] Fallo en sincronización', ['error' => $e->getMessage()]);
            }
        }

        return $customer;
    }
}