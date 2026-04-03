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

        if ($data->guestUuid) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                    $this->cartService->fusionGuestCart(
                        (string) Auth::guard('customer')->id(),
                        $data->guestUuid
                    );
                });
            } catch (\Exception $e) {
                Log::error('Fallo crítico en fusión de carrito post-login', ['error' => $e->getMessage()]);
                // No detenemos el login, pero registramos la falla de integridad.
            }
        }

        return true;
    }
}