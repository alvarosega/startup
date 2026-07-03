<?php

declare(strict_types=1);

namespace App\Exceptions\Customer\Auth;

use App\Exceptions\DomainException;

final class OutOfCoverageException extends DomainException
{
    public function __construct(float $lat, float $lng, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Las coordenadas proporcionadas (lat: {$lat}, lng: {$lng}) no están dentro del área de cobertura de ninguna sucursal.",
            $code,
            $previous
        );
    }
}