<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

final class CustomerRegistrationIdentityRule implements ValidationRule
{
    public function __construct(
        private readonly string $column
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $record = DB::table('customers')
            ->select(['deleted_epoch'])
            ->where($this->column, (string) $value)
            ->first();

        if ($record !== null) {
            $label = $this->column === 'email' ? 'correo electrónico' : 'número telefónico';

            if ((int) $record->deleted_epoch === 0) {
                $fail("El {$label} ya está registrado y activo en la plataforma.");
                return;
            }

            $fail("Esta cuenta fue dada de baja previamente. Comuníquese con soporte para su reactivación.");
        }
    }
}