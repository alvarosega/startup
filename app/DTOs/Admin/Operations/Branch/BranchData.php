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
        public float $deliveryBaseFee,
        public float $deliveryPricePerKm,
        public float $surgeMultiplier,
        public float $minOrderAmount,
        public float $smallOrderFee,
        public float $baseServiceFeePercentage
    ) {}

    /**
     * Factoría estática acoplada a los FormRequests validados para operaciones de creación y actualización.
     */
    public static function fromRequest(FormRequest $request): self
    {
        $validated = $request->validated();

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        // Construcción segura del WKT para el punto espacial (POINT: longitud latitud)
        $locationWkt = "POINT({$lng} {$lat})";

        // Mapeo directo y ordenamiento de coordenadas espaciales nativas basadas en [longitud, latitud]
        $coveragePolygonWkt = null;
        if (!empty($validated['coverage_polygon']) && is_array($validated['coverage_polygon'])) {
            $points = [];
            foreach ($validated['coverage_polygon'] as $coord) {
                if (isset($coord[0], $coord[1])) {
                    // QA envía directamente [longitud, latitud], se concatena de forma nativa para MySQL
                    $points[] = "{$coord[0]} {$coord[1]}";
                }
            }
            if (!empty($points)) {
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
            deliveryBaseFee: (float) ($validated['delivery_base_fee'] ?? 0.00),
            deliveryPricePerKm: (float) ($validated['delivery_price_per_km'] ?? 0.00),
            surgeMultiplier: (float) ($validated['surge_multiplier'] ?? 1.00),
            minOrderAmount: (float) ($validated['min_order_amount'] ?? 0.00),
            smallOrderFee: (float) ($validated['small_order_fee'] ?? 0.00),
            baseServiceFeePercentage: (float) ($validated['base_service_fee_percentage'] ?? 0.00)
        );
    }
}