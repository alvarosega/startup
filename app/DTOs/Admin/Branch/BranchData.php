<?php

namespace App\DTOs\Admin\Branch;

use Illuminate\Http\Request;

readonly class BranchData
{
    public function __construct(
        public string $name,
        public string $city,
        public ?string $phone,
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
        public ?array $coverage_polygon,
        public ?array $opening_hours,
        public bool $is_default = false,
        public bool $is_active = true,

    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            city: $request->validated('city', 'La Paz'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
            latitude: $request->has('latitude') ? (float) $request->validated('latitude') : null,
            longitude: $request->has('longitude') ? (float) $request->validated('longitude') : null,
            coverage_polygon: $request->validated('coverage_polygon'),
            opening_hours: $request->validated('opening_hours'),
            is_default: (bool) $request->validated('is_default', false),
            is_active: (bool) $request->validated('is_active', true),
        );
    }
}