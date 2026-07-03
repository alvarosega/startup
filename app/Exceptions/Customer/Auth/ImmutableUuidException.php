<?php

declare(strict_types=1);

namespace App\Exceptions\Customer\Auth;

use App\Exceptions\DomainException;

final class ImmutableUuidException extends DomainException
{
    public function __construct(string $field = 'id', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Violación de Integridad: No está permitido modificar el identificador '{$field}'.",
            $code,
            $previous
        );
    }
}