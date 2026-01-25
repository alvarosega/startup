<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'], 
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'], // Nullable porque Web lo inyecta o DTO lo asume
        ];
    }

    // Rate Limiting: 5 intentos por minuto por IP/Email
    public function ensureIsNotRateLimited(): void
    {
        $key = Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());

        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function hitRateLimiter(): void
    {
        $key = Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
        RateLimiter::hit($key);
    }
}
