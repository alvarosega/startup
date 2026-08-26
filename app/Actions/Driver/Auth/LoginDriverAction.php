<?php

declare(strict_types=1);

namespace App\Actions\Driver\Auth;

use App\DTOs\Driver\Auth\LoginDriverData;
use App\Models\Users\Driver;
use App\Models\Users\DriverAccessLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginDriverAction
{
    public function execute(LoginDriverData $data): Driver
    {
        $ip = request()->ip() ?? '127.0.0.1';
        $userAgent = request()->userAgent();
        $throttleKey = Str::transliterate(Str::lower($data->phone) . '|' . $ip);

        // 1. Throttling (5 intentos)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            DriverAccessLog::create([
                'driver_id'       => null,
                'phone_attempted' => $data->phone,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'status'          => 'RATE_LIMITED',
                'failure_reason'  => "Bloqueo por exceso de intentos. Reintente en {$seconds}s.",
            ]);

            throw ValidationException::withMessages([
                'phone' => "Demasiados intentos de acceso fallidos. Reintente en {$seconds} segundos.",
            ]);
        }

        // 2. Comprobar existencia
        /** @var Driver|null $driver */
        $driver = Driver::query()
            ->where('phone', $data->phone)
            ->where('deleted_epoch', 0)
            ->first();

        if (!$driver || !Hash::check($data->password, $driver->password)) {
            RateLimiter::hit($throttleKey, 60);

            DriverAccessLog::create([
                'driver_id'       => $driver?->id,
                'phone_attempted' => $data->phone,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'status'          => 'INVALID_CREDENTIALS',
                'failure_reason'  => 'Contraseña incorrecta o repartidor no registrado.',
            ]);

            throw ValidationException::withMessages([
                'phone' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // 3. Bloqueo estricto para cuentas suspendidas
        $statusValue = is_object($driver->status) ? $driver->status->value : (string) $driver->status;

        if ($statusValue === 'suspended') {
            DriverAccessLog::create([
                'driver_id'       => $driver->id,
                'phone_attempted' => $data->phone,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'status'          => 'SUSPENDED_ACCOUNT',
                'failure_reason'  => 'Cuenta suspendida administrativamente.',
            ]);

            throw ValidationException::withMessages([
                'phone' => 'Acceso denegado: Su cuenta de repartidor se encuentra suspendida.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // 4. Auditoría de éxito
        DriverAccessLog::create([
            'driver_id'       => $driver->id,
            'phone_attempted' => $data->phone,
            'ip_address'      => $ip,
            'user_agent'      => $userAgent,
            'status'          => 'SUCCESS',
            'failure_reason'  => null,
        ]);

        $driver->recordSuccessfulLogin();
        Auth::guard('driver')->login($driver, $data->remember);

        return $driver;
    }
}