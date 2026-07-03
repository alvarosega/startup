<?php

declare(strict_types=1);

namespace App\Exceptions\Customer\Auth;

use App\Exceptions\DomainException;

final class PhysicalDeletionProhibitedException extends DomainException
{
    public function __construct(string $entity = 'registro', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Operación Prohibida: No se permite la eliminación física de {$entity}. Utilice el Action de borrado lógico correspondiente.",
            $code,
            $previous
        );
    }
}