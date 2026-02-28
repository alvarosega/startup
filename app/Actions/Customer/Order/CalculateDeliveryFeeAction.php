<?php

namespace App\Actions\Customer\Order;

use App\Models\Branch;
use App\Models\CustomerAddress;
use App\Models\Customer;

class CalculateDeliveryFeeAction
{
    // Constantes Operativas
    private const EARTH_RADIUS_KM = 6371;
    // Usamos 1.40 debido a la topografía compleja analizada (La Paz/Bolivia)
    private const URBAN_TORTUOSITY_FACTOR = 1.40; 

    /**
     * Retorna el desglose financiero exacto para el Checkout.
     */
    public function execute(Branch $branch, CustomerAddress $address, Customer $customer, float $subtotal): array
    {
        // 1. Validar Coordenadas (Fallback de seguridad)
        if (!$branch->latitude || !$branch->longitude || !$address->latitude || !$address->longitude) {
            return $this->fallbackContingency($branch, $subtotal);
        }

        // 2. Cálculo Geográfico
        $haversineDistance = $this->calculateHaversineDistance(
            $branch->latitude, $branch->longitude,
            $address->latitude, $address->longitude
        );
        $realDistanceKm = $haversineDistance * self::URBAN_TORTUOSITY_FACTOR;

        // 3. Cálculo de Tarifa de Envío (Delivery Fee)
        $distanceFee = $branch->delivery_base_fee + ($realDistanceKm * $branch->delivery_price_per_km);
        $surgedFee = $distanceFee * $branch->surge_multiplier;
        
        $penaltyFee = ($subtotal < $branch->min_order_amount) ? $branch->small_order_fee : 0.00;
        
        $totalDeliveryFee = round($surgedFee + $penaltyFee, 2);

        // 4. Cálculo de Tarifa de Servicio (Service Fee) con Gamificación
        $baseServiceFee = $subtotal * ($branch->base_service_fee_percentage / 100);
        
        // El trust_score actúa como porcentaje de descuento (0 a 100)
        $trustScore = max(0, min(100, (int) $customer->trust_score)); 
        $loyaltyDiscountMultiplier = 1 - ($trustScore / 100);
        
        $totalServiceFee = round($baseServiceFee * $loyaltyDiscountMultiplier, 2);

        $baseServiceFee = $subtotal * ($branch->base_service_fee_percentage / 100);
        $trustScore = max(0, min(100, (int) $customer->trust_score)); 
        $loyaltyDiscountMultiplier = 1 - ($trustScore / 100);
        $totalServiceFee = round($baseServiceFee * $loyaltyDiscountMultiplier, 2);
        
        return [
            'distance_km' => round($realDistanceKm, 2),
            'delivery_fee' => $totalDeliveryFee,
            'original_service_fee' => round($baseServiceFee, 2), // <--- NUEVO
            'service_fee' => $totalServiceFee,
            'savings' => round($baseServiceFee - $totalServiceFee, 2), // <--- NUEVO
            'is_penalty_applied' => $penaltyFee > 0,
            'penalty_amount' => round($penaltyFee, 2),
            'total_logistics' => round($totalDeliveryFee + $totalServiceFee, 2)
        ];
    }

    /**
     * Algoritmo Matemático Esférico
     */
    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * asin(sqrt($a));
        
        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Contingencia si faltan coordenadas (Cobro Base estricto)
     */
    private function fallbackContingency(Branch $branch, float $subtotal): array
    {
        $penalty = ($subtotal < $branch->min_order_amount) ? $branch->small_order_fee : 0.00;
        
        return [
            'distance_km' => 0.00,
            'delivery_fee' => round($branch->delivery_base_fee + $penalty, 2),
            'service_fee' => round($subtotal * ($branch->base_service_fee_percentage / 100), 2),
            'is_penalty_applied' => $penalty > 0,
            'penalty_amount' => round($penalty, 2),
            'total_logistics' => round($branch->delivery_base_fee + $penalty + ($subtotal * ($branch->base_service_fee_percentage / 100)), 2)
        ];
    }
}