<?php

namespace App\Services\Order;

use App\Models\{Branch, CustomerAddress, Customer};

class OrderFinancialService
{
    private const EARTH_RADIUS_KM = 6371;
    private const URBAN_TORTUOSITY_FACTOR = 1.40; // Contexto La Paz
    private const MAX_DELIVERY_FEE_THRESHOLD = 200.00; // Techo de seguridad

    public function calculate(Branch $branch, ?CustomerAddress $address, Customer $customer, float $itemsSubtotal, string $type): array
    {
        $deliveryFee = 0.00;
        $distanceKm = 0.00;
        $isWithinSafetyCeiling = true;

        // 1. Lógica de Envío (Solo si es 'delivery')
        if ($type === 'delivery' && $address) {
            $distanceKm = $this->calculateRealDistance($branch, $address);
            
            $baseDistanceFee = $branch->delivery_base_fee + ($distanceKm * $branch->delivery_price_per_km);
            $deliveryFee = round($baseDistanceFee * $branch->surge_multiplier, 2);

            // Penalización por pedido pequeño
            if ($itemsSubtotal < $branch->min_order_amount) {
                $deliveryFee += $branch->small_order_fee;
            }

            // Protección: Validar techo de seguridad
            if ($deliveryFee > self::MAX_DELIVERY_FEE_THRESHOLD) {
                $isWithinSafetyCeiling = false;
            }
        }

        // 2. Lógica de Servicio (Comisión Digital + Trust Score)
        $baseServiceFee = $itemsSubtotal * ($branch->base_service_fee_percentage / 100);
        $trustDiscount = max(0, min(100, (int) $customer->trust_score)) / 100;
        $finalServiceFee = round($baseServiceFee * (1 - $trustDiscount), 2);

        return [
            'type'            => $type,
            'distance_km'     => round($distanceKm, 2),
            'items_subtotal'  => round($itemsSubtotal, 2),
            'delivery_fee'    => (float) $deliveryFee,
            'service_fee'     => (float) $finalServiceFee,
            'total_amount'    => round($itemsSubtotal + $deliveryFee + $finalServiceFee, 2),
            'savings_loyalty' => round($baseServiceFee - $finalServiceFee, 2),
            'is_available'    => $isWithinSafetyCeiling,
            'error_message'   => $isWithinSafetyCeiling ? null : "Distancia fuera de rango operativo estándar."
        ];
    }

    private function calculateRealDistance($branch, $address): float
    {
        if (!$branch->latitude || !$address->latitude) return 0.00;

        $dLat = deg2rad($address->latitude - $branch->latitude);
        $dLon = deg2rad($address->longitude - $branch->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($branch->latitude)) * cos(deg2rad($address->latitude)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $haversine = self::EARTH_RADIUS_KM * (2 * asin(sqrt($a)));
        
        return $haversine * self::URBAN_TORTUOSITY_FACTOR;
    }
}