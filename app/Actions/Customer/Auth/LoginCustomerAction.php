<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\DTOs\Customer\Auth\LoginCustomerData;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class LoginCustomerAction
{
    public function __construct(protected CartService $cartService) {}

    public function execute(LoginCustomerData $dto): ActionResult
    {
        $credentials = [
            'phone' => $dto->phone,
            'password' => $dto->password
        ];

        if (!Auth::guard('customer')->attempt($credentials, $dto->remember)) {
            return ActionResult::failure('Las credenciales proporcionadas no coinciden con nuestros registros.');
        }

        /** @var \App\Models\Users\Customer $customer */
        $customer = Auth::guard('customer')->user();

        if (!$customer->is_active) {
            Auth::guard('customer')->logout();
            return ActionResult::failure('Acceso denegado: Su cuenta de cliente se encuentra inactiva o suspendida.');
        }

        if ($dto->guestUuid) {
            DB::transaction(function () use ($customer, $dto): void {
                $this->cartService->fusionGuestCart((string) $customer->id, $dto->guestUuid);
            });
        }

        return ActionResult::success($customer);
    }
}