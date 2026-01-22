<?php

namespace App\Observers;

use App\Models\InventoryLot;
use Illuminate\Support\Str;

class InventoryLotObserver
{
    public function creating(InventoryLot $lot): void
    {
        // Generar código único: LOT-YYMM-RANDOM (Ej: LOT-2401-X9Z1)
        if (empty($lot->lot_code)) {
            $lot->lot_code = 'LOT-' . now()->format('ym') . '-' . Str::upper(Str::random(4));
        }
    }
}