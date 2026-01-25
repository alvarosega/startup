<?php

namespace App\DTOs\Branch;

class BranchData
{
    public function __construct(
        public readonly string $name,
        public readonly string $city,
        public readonly ?string $phone,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly array $coveragePolygon,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            city: $request->validated('city'),
            phone: $request->validated('phone'),
            address: $request->validated('address'),
            latitude: (float) $request->validated('latitude'),
            longitude: (float) $request->validated('longitude'),
            coveragePolygon: $request->validated('coverage_polygon') ?? [],
            isActive: $request->boolean('is_active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'city' => $this->city,
            'phone' => $this->phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coverage_polygon' => $this->coveragePolygon,
            'is_active' => $this->isActive,
        ];
    }
}