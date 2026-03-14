<?php

namespace App\Services\Order;

use App\Models\{Branch, Customer};

class OrderFinancialService
{
    private const EARTH_RADIUS_KM = 6371;
    private const URBAN_TORTUOSITY_FACTOR = 1.40; // Contexto La Paz
    private const MAX_DELIVERY_FEE_THRESHOLD = 200.00; // Blindaje económico

    public function calculate(Branch $branch, Customer $customer, float $itemsSubtotal, string $type): array
    {
        $deliveryFee = 0.00;
        $distanceKm = 0.00;
        $isAvailable = true;
        $errorMessage = null;

        // 1. Lógica de Envío (Solo si es 'delivery')
        if ($type === 'delivery') {
            // Validar que el cliente tenga coordenadas sincronizadas
            if (!$customer->latitude || !$customer->longitude) {
                return $this->errorResponse("Ubicación del cliente no configurada.");
            }

            $distanceKm = $this->calculateRealDistance(
                $branch->latitude, $branch->longitude,
                $customer->latitude, $customer->longitude
            );
            
            $baseDistanceFee = $branch->delivery_base_fee + ($distanceKm * $branch->delivery_price_per_km);
            $deliveryFee = round($baseDistanceFee * $branch->surge_multiplier, 2);

            // Penalización por pedido mínimo
            if ($itemsSubtotal < $branch->min_order_amount) {
                $deliveryFee += $branch->small_order_fee;
            }

            // Validación de Techo de Seguridad (200 Bs)
            if ($deliveryFee > self::MAX_DELIVERY_FEE_THRESHOLD) {
                $isAvailable = false;
                $errorMessage = "La distancia excede el límite operativo permitido.";
            }
        }

        // 2. Lógica de Servicio (Comisión + Trust Score Manual)
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
            'is_available'    => $isAvailable,
            'error_message'   => $errorMessage
        ];
    }

    private function calculateRealDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $haversine = self::EARTH_RADIUS_KM * (2 * asin(sqrt($a)));
        
        return $haversine * self::URBAN_TORTUOSITY_FACTOR;
    }

    private function errorResponse(string $msg): array
    {
        return [
            'is_available' => false,
            'error_message' => $msg,
            'total_amount' => 0
        ];
    }
}