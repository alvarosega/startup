<?php

declare(strict_types=1);

namespace App\DTOs\Admin\Operations\Branch;

use Illuminate\Foundation\Http\FormRequest;

readonly class BranchData
{
    public function __construct(
        public string $name,
        public string $city,
        public ?string $phone,
        public ?string $address,
        public float $latitude,
        public string $locationWkt,
        public ?string $coveragePolygonWkt,
        public bool $isDefault,
        public bool $isActive,
        public bool $isOperationalPaused,
        public ?int $maxActiveOrders,
        public float $deliveryBaseFee,
        public float $deliveryPricePerKm,
        public float $surgeMultiplier,
        public float $minOrderAmount,
        public float $smallOrderFee,
        public float $baseServiceFeePercentage
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        $locationWkt = "POINT({$lng} {$lat})";
        
        maxActiveOrders: isset($validated['max_active_orders']) && $validated['max_active_orders'] !== null ? (int) $validated['max_active_orders'] : null,
        $coveragePolygonWkt = null;
        if (!empty($validated['coverage_polygon']) && is_array($validated['coverage_polygon'])) {
            $points = [];
            foreach ($validated['coverage_polygon'] as $coord) {
                if (isset($coord[0], $coord[1])) {
                    $points[] = "{$coord[0]} {$coord[1]}";
                }
            }

            if (count($points) >= 3) {
                // Forzar cierre de polígono OGC (primer punto = último punto)
                if ($points[0] !== $points[count($points) - 1]) {
                    $points[] = $points[0];
                }
                $polygonString = implode(',', $points);
                $coveragePolygonWkt = "POLYGON(({$polygonString}))";
            }
        }

        return new self(
            name: (string) $validated['name'],
            city: (string) ($validated['city'] ?? 'La Paz'),
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            address: isset($validated['address']) ? (string) $validated['address'] : null,
            latitude: $lat,
            locationWkt: $locationWkt,
            coveragePolygonWkt: $coveragePolygonWkt,
            isDefault: (bool) $request->boolean('is_default'),
            isActive: (bool) $request->boolean('is_active', true),
            isOperationalPaused: (bool) $request->boolean('is_operational_paused', false),
            deliveryBaseFee: (float) ($validated['delivery_base_fee'] ?? 0.00),
            deliveryPricePerKm: (float) ($validated['delivery_price_per_km'] ?? 0.00),
            surgeMultiplier: (float) ($validated['surge_multiplier'] ?? 1.00),
            minOrderAmount: (float) ($validated['min_order_amount'] ?? 0.00),
            smallOrderFee: (float) ($validated['small_order_fee'] ?? 0.00),
            baseServiceFeePercentage: (float) ($validated['base_service_fee_percentage'] ?? 0.00)
        );
    }
}