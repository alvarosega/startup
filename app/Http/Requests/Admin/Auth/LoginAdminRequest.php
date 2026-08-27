<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Auth;

use App\Models\Users\Admin;
use App\Models\Users\AdminAccessLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginAdminRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function checkRateLimit(): void
    {
        $key = $this->getThrottleKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            AdminAccessLog::create([
                'admin_id'        => Admin::where('email', (string) $this->input('email'))->value('id'),
                'email_attempted' => (string) $this->input('email'),
                'ip_address'      => (string) ($this->ip() ?? '127.0.0.1'),
                'user_agent'      => (string) ($this->userAgent() ?? 'Unknown'),
                'status'          => 'RATE_LIMITED',
                'created_at'      => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => (int) ceil($seconds / 60),
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
        RateLimiter::clear($this->getThrottleKey());
    }

protected function getThrottleKey(): string
{
    return Str::transliterate(Str::lower((string) $this->input('email')));
}
}