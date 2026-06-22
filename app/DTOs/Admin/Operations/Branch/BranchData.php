<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Operations\Branch;

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
        public bool $is_default,
        public bool $is_active,
        public float $delivery_base_fee,
        public float $delivery_price_per_km,
        public float $surge_multiplier,
        public float $min_order_amount,
        public float $small_order_fee,
        public float $base_service_fee_percentage
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            city: (string) ($validated['city'] ?? 'La Paz'),
            phone: $validated['phone'] ?? null,
            address: $validated['address'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            coverage_polygon: $validated['coverage_polygon'] ?? null,
            opening_hours: $validated['opening_hours'] ?? null,
            is_default: $request->boolean('is_default'),
            is_active: $request->boolean('is_active', true),
            delivery_base_fee: (float) ($validated['delivery_base_fee'] ?? 0.00),
            delivery_price_per_km: (float) ($validated['delivery_price_per_km'] ?? 0.00),
            surge_multiplier: (float) ($validated['surge_multiplier'] ?? 1.00),
            min_order_amount: (float) ($validated['min_order_amount'] ?? 0.00),
            small_order_fee: (float) ($validated['small_order_fee'] ?? 0.00),
            base_service_fee_percentage: (float) ($validated['base_service_fee_percentage'] ?? 0.00)
        );
    }
}