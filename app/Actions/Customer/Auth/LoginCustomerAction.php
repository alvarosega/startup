<?php

namespace App\Actions\Customer\Auth;

use App\DTOs\Customer\Auth\LoginCustomerData; 
use App\Services\Cart\CartService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginCustomerAction
{
    public function __construct(
        protected ShopContextService $contextService,
        protected CartService $cartService
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
            $this->cartService->fusionGuestCart(
                (string) Auth::guard('customer')->id(),
                $data->guestUuid
            );
        }

        return true;
    }
}