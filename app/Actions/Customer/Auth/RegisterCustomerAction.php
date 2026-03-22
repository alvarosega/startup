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
use App\Http\Resources\Customer\Branch\BranchResource;
class RegisterCustomerAction
{
    public function __construct(
        protected BranchCoverageService $geoService,
        protected SyncGuestCartAction $syncAction,
        protected ShopContextService $shopContext
    ) {}

    public function execute(RegisterCustomerData $data, ?string $idempotencyKey = null): Customer
    {
        // 1. Idempotencia: Evitar duplicados por doble clic
        if ($idempotencyKey && $cachedUser = cache()->get("reg_key_{$idempotencyKey}")) {
            return $cachedUser;
        }
    
        $customer = DB::transaction(function () use ($data) {
            // Validación de Cobertura
            $identifiedBranch = $this->geoService->identifyBranch($data->latitude, $data->longitude);
            $assignedBranchId = $identifiedBranch ?? $this->shopContext->getDefaultBranchId();
    
            $user = Customer::create([
                'phone'        => $data->phone,
                'email'        => $data->email,
                'password'     => Hash::make($data->password),
                'country_code' => strtoupper($data->countryCode),
                'branch_id'    => $assignedBranchId,
                'latitude'     => $data->latitude,
                'longitude'    => $data->longitude,
                'is_active'    => false, // Zero-Trust: Inactivo por defecto
            ]);
    
            $user->profile()->create([
                'first_name' => $data->firstName,
                'last_name'  => $data->lastName,
                'avatar_type' => $data->avatarType,
                'avatar_source' => $data->avatarSource ?? 'avatar_1.svg',
            ]);
    
            $user->addresses()->create([
                'alias'      => $data->alias ?? 'Casa',
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
    
        // 2. Login Automático
        Auth::guard('customer')->login($customer);
    
        // 3. Side Effect: Sincronización de Carrito (No debe matar el proceso si falla)
        if ($data->guestUuid) {
            try {
                $this->syncAction->execute(new SyncCartDTO(
                    customerId: $customer->id,
                    guestUuid:  $data->guestUuid,
                    branchId:   $customer->branch_id
                ));
            } catch (\Exception $e) {
                Log::error("[CartSync] Error no crítico post-registro: " . $e->getMessage());
            }
        }
    
        // Guardar en caché para idempotencia
        if ($idempotencyKey) {
            cache()->put("reg_key_{$idempotencyKey}", $customer, 600);
        }
    
        return $customer;
    }
}