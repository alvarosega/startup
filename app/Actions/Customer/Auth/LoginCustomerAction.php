<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Architecture\ActionResult;
use App\DTOs\Customer\Auth\LoginCustomerData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginCustomerAction
{
    public function execute(LoginCustomerData $dto): ActionResult
    {
        $throttleKey = Str::transliterate(Str::lower($dto->phone) . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'phone' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $credentials = [
            'phone' => $dto->phone,
            'password' => $dto->password,
        ];

        if (!Auth::guard('customer')->attempt($credentials, $dto->remember)) {
            RateLimiter::hit($throttleKey, 60);
            return ActionResult::failure('Las credenciales proporcionadas no coinciden con nuestros registros.');
        }

        RateLimiter::clear($throttleKey);

        /** @var \App\Models\Users\Customer $customer */
        $customer = Auth::guard('customer')->user();

        if (!$customer->is_active) {
            Auth::guard('customer')->logout();
            return ActionResult::failure('Acceso denegado: Su cuenta de cliente se encuentra inactiva o suspendida.');
        }

        DB::transaction(function () use ($customer): void {
            $customer->update(['last_login_at' => now()]);
        });

        return ActionResult::success($customer);
    }
}