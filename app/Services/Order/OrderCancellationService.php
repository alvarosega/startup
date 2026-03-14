<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Services\Inventory\InventoryOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderCancellationService
{
    public function __construct(protected InventoryOrchestrator $inventory) {}

    public function handleExpiration(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // 1. Cambiar estado a 'expired'
            $order->update(['status' => 'expired']);

            // 2. Liberar Stock de cada item
            foreach ($order->items as $item) {
                $this->inventory->release($item->sku_id, $order->branch_id, $item->quantity);
            }

            Log::info("Orden {$order->code} expirada. Stock liberado.");
        });
    }
}