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
        public float $delivery_base_fee = 0.00,
        public float $delivery_price_per_km = 0.00,
        public float $surge_multiplier = 1.00,
        public float $min_order_amount = 0.00,
        public float $small_order_fee = 0.00,
        public float $base_service_fee_percentage = 0.00,

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
            delivery_base_fee: (float) $request->validated('delivery_base_fee', 0.00),
            delivery_price_per_km: (float) $request->validated('delivery_price_per_km', 0.00),
            surge_multiplier: (float) $request->validated('surge_multiplier', 1.00),
            min_order_amount: (float) $request->validated('min_order_amount', 0.00),
            small_order_fee: (float) $request->validated('small_order_fee', 0.00),
            base_service_fee_percentage: (float) $request->validated('base_service_fee_percentage', 0.00),
        );
    }
}