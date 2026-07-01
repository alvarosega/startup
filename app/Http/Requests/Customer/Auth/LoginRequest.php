<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer\Auth;

use App\Traits\ValidatesGlobalIdentity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    use ValidatesGlobalIdentity;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeIdentityData();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'guest_client_uuid' => ['nullable', 'uuid']
        ];
    }

    public function checkRateLimit(): void
    {
        $throttleKey = $this->getThrottleKey();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'phone' => __('auth.throttle', ['seconds' => $seconds]),
            ]);
        }
    }

    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->getThrottleKey(), 60);
    }

    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->getThrottleKey());
    }

    private function getThrottleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('phone')) . '|' . $this->ip());
    }
}