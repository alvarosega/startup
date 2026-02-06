<?php

namespace App\DTOs\Client;

use Illuminate\Http\Request;

class AddressData
{
    public function __construct(
        public readonly string $alias,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?string $reference,
        public readonly bool $isDefault,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            alias: $request->input('alias'),
            address: $request->input('address'),
            // Forzamos float para cálculos geoespaciales precisos
            latitude: (float) $request->input('latitude'),
            longitude: (float) $request->input('longitude'),
            reference: $request->input('details'), // Mapeamos 'details' del form a 'reference' de la DB
            isDefault: $request->boolean('is_default', false),
        );
    }
}