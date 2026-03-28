<?php

namespace App\Actions\Customer\Auth;

use App\DTOs\Customer\Auth\LoginCustomerData; 
use App\Actions\Customer\Cart\SyncGuestCartAction;
use App\DTOs\Customer\Cart\SyncCartDTO;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginCustomerAction
{
    public function __construct(
        protected SyncGuestCartAction $syncAction,
        protected ShopContextService $contextService
    ) {}

    public function execute(LoginCustomerData $data): bool
    {
        // 1. Intento de autenticación en el Silo Customer
        if (!Auth::guard('customer')->attempt([
            'phone'    => $data->phone, 
            'password' => $data->password
        ], $data->remember)) {
            throw ValidationException::withMessages([
                'phone' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
            ]);
        }

        // 2. Telemetría de acceso
        Log::info('Customer Login Success', ['uuid_sync' => $data->guestUuid]);

        // 3. Protocolo de Fusión de Carrito (Merge)
        if ($data->guestUuid) {
            $this->syncAction->execute(new SyncCartDTO(
                customerId: (string) Auth::guard('customer')->id(),
                guestUuid:  $data->guestUuid,
                branchId:   $this->contextService->getActiveBranchId()
            ));
        }

        return true;
    }
}