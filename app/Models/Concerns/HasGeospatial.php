<?php

namespace App\Models\Concerns;

trait HasGeospatial
{
    /**
     * Algoritmo Ray-Casting para verificar si una coordenada está dentro de un polígono.
     */
    public function isPointInPolygon(float $pointLat, float $pointLng, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);
        $j = $count - 1;

        for ($i = 0; $i < $count; $i++) {
            $vertex1 = $polygon[$i];
            $vertex2 = $polygon[$j];

            // Verifica intersecciones de vectores
            $intersect = (($vertex1['lng'] > $pointLng) != ($vertex2['lng'] > $pointLng))
                && ($pointLat < ($vertex2['lat'] - $vertex1['lat']) * ($pointLng - $vertex1['lng']) / ($vertex2['lng'] - $vertex1['lng']) + $vertex1['lat']);

            if ($intersect) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}