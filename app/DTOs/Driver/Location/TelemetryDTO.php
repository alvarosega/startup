<?php

namespace App\DTOs\Driver\Location;

readonly class TelemetryDTO
{
    public function __construct(
        public string $driverId,
        public float $latitude,
        public float $longitude
    ) {}
}