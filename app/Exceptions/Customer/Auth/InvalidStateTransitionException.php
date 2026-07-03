<?php

declare(strict_types=1);

namespace App\Exceptions\Customer\Auth;

use App\Exceptions\DomainException;

final class InvalidStateTransitionException extends DomainException
{
    public function __construct(string $message = 'Transición de estado no permitida.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}