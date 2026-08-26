<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Models\Users\Customer;
use App\Architecture\ActionResult;
use App\DTOs\Customer\Auth\LoginCustomerData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\Users\CustomerAccessLog;


final class LoginCustomerAction
{
    public function execute(LoginCustomerData $dto): ActionResult
    {
        $ip = request()->ip() ?? '127.0.0.1';
        $userAgent = request()->userAgent();
        $throttleKey = Str::transliterate(Str::lower($dto->phone) . '|' . $ip);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            \App\Models\Users\CustomerAccessLog::create([
                'customer_id'     => null,
                'phone_attempted' => $dto->phone,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'status'          => 'RATE_LIMITED',
                'failure_reason'  => "Bloqueo por exceso de intentos. Reintente en {$seconds}s.",
            ]);

            return ActionResult::failure("Demasiados intentos de acceso fallidos. Reintente en {$seconds} segundos.");
        }

        // Comprobar existencia activa de la cuenta antes de validar hash
        /** @var Customer|null $customer */
        $customer = Customer::where('phone', $dto->phone)
            ->where('deleted_epoch', 0)
            ->first();

        if (!$customer || !Hash::check($dto->password, $customer->password)) {
            RateLimiter::hit($throttleKey, 60);

            \App\Models\Users\CustomerAccessLog::create([
                'customer_id'     => $customer?->id,
                'phone_attempted' => $dto->phone,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'status'          => 'INVALID_CREDENTIALS',
                'failure_reason'  => 'Contraseña incorrecta o usuario no registrado.',
            ]);

            return ActionResult::failure('Las credenciales proporcionadas no coinciden con nuestros registros.');
        }

        if (!$customer->is_active) {
            \App\Models\Users\CustomerAccessLog::create([
                'customer_id'     => $customer->id,
                'phone_attempted' => $dto->phone,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'status'          => 'INACTIVE_ACCOUNT',
                'failure_reason'  => 'Cuenta inactiva o suspendida.',
            ]);

            return ActionResult::failure('Acceso denegado: Su cuenta de cliente se encuentra inactiva o suspendida.');
        }

        RateLimiter::clear($throttleKey);

        \App\Models\Users\CustomerAccessLog::create([
            'customer_id'     => $customer->id,
            'phone_attempted' => $dto->phone,
            'ip_address'      => $ip,
            'user_agent'      => $userAgent,
            'status'          => 'SUCCESS',
            'failure_reason'  => null,
        ]);

        $customer->recordSuccessfulLogin();
        Auth::guard('customer')->login($customer, $dto->remember);

        return ActionResult::success($customer);
    }
}