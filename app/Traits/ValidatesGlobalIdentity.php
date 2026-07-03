<?php

declare(strict_types=1);

namespace App\Traits;

use App\Rules\GlobalUniqueIdentity;

trait ValidatesGlobalIdentity
{
    protected function normalizeIdentityData(): void
    {
        if ($this->has('phone') && !empty($this->input('phone'))) {
            $cleanPhone = preg_replace('/[^\+0-9]/', '', (string) $this->input('phone'));
            if (!str_starts_with($cleanPhone, '+')) {
                $cleanPhone = '+' . $cleanPhone;
            }
            $this->merge(['phone' => $cleanPhone]);
        }
    }

    /**
     * @return array<int, mixed>
     */
    protected function globalPhoneRules(string $table, ?string $ignoreId = null): array
    {
        return [
            'required',
            'string',
            'min:8',
            'max:20',
            new GlobalUniqueIdentity($table, 'phone', $ignoreId),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function globalEmailRules(string $table, ?string $ignoreId = null): array
    {
        return [
            'required',
            'email',
            'max:255',
            new GlobalUniqueIdentity($table, 'email', $ignoreId),
        ];
    }
}