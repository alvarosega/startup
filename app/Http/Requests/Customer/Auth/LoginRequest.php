<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        // 1. Normalización de identidad (Trait)
        $this->normalizeIdentityData();

        // 2. RECUPERACIÓN DE EMERGENCIA
        if (!$this->filled('guest_client_uuid')) {
            $this->merge([
                'guest_client_uuid' => session('guest_client_uuid')
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'phone'             => ['required', 'string'],
            'password'          => ['required', 'string'],
            'remember'          => ['boolean'],
            'guest_client_uuid' => ['nullable', 'string', 'uuid'],
        ];
    }

    /**
     * Comprueba si el cliente ha superado el límite de intentos de acceso.
     */
    public function checkRateLimit(): void
    {
        $key = $this->getThrottleKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'phone' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    /**
     * Registra un intento fallido en el limitador de peticiones.
     */
    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->getThrottleKey(), 60);
    }

    /**
     * Limpia los intentos de inicio de sesión tras una autenticación exitosa.
     */
    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->getThrottleKey());
    }

    /**
     * Genera la llave única de estrangulamiento basada en el teléfono e IP.
     */
    protected function getThrottleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('phone')) . '|' . $this->ip());
    }
}