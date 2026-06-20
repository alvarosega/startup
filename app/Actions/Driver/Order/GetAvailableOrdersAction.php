<?php

declare(strict_types=1);

namespace App\Actions\Driver\Order;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class GetAvailableOrdersAction
{
    public function execute()
    {
        $driver = Auth::guard('driver')->user();

        return Order::with(['branch:id,name,address'])
            ->whereIn('status', ['preparing', 'ready_for_dispatch']) // Estados de visibilidad
            ->whereNull('driver_id') // Solo lo que nadie ha tomado
            ->where('branch_id', $driver->branch_id)
            ->select(['id', 'code', 'status', 'total_amount', 'delivery_type', 'branch_id', 'created_at'])
            ->orderByRaw("CASE WHEN status = 'ready_for_dispatch' THEN 1 ELSE 2 END") // Prioridad a lo que ya está listo
            ->orderBy('created_at', 'desc')
            ->get();
    }
}