<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginAdminRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function checkRateLimit(): void
    {
        $key = $this->getThrottleKey();

        // Corrección: check puro sin incremento automático
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->getThrottleKey(), 60);
    }

    public function clearRateLimiter(): void
    {
        // Obligatorio para limpiar el historial tras un login exitoso
        RateLimiter::clear($this->getThrottleKey());
    }

    protected function getThrottleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}