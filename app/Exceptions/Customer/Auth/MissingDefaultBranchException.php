<?php

declare(strict_types=1);

namespace App\Exceptions\Customer\Auth;

use App\Exceptions\DomainException;

final class MissingDefaultBranchException extends DomainException
{
    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            'Error Crítico: No existe una sucursal activa por defecto configurada en el sistema.',
            $code,
            $previous
        );
    }
}