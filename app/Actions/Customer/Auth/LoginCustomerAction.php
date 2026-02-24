<?php

namespace App\Actions\Customer\Auth;

use App\DTOs\Customer\Auth\LoginCustomerData; 
use App\Actions\Customer\Cart\SyncGuestCartAction;
use App\DTOs\Customer\Cart\SyncCartDTO; // <--- OBLIGATORIO
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
        if (!Auth::guard('customer')->attempt([
            'phone'    => $data->phone, 
            'password' => $data->password
        ], $data->remember)) {
            throw ValidationException::withMessages(['phone' => 'Credenciales inválidas.']);
        }

        // Si esto sale null, el problema es el DTO o el Request
        Log::info('Login exitoso', ['uuid_en_dto' => $data->guestUuid]);

        if ($data->guestUuid) {
            // USO OBLIGATORIO DE $this->
            $this->syncAction->execute(new SyncCartDTO(
                customerId: Auth::guard('customer')->id(),
                guestUuid:  $data->guestUuid,
                branchId:   $this->contextService->getActiveBranchId()
            ));
        }

        return true;
    }
}