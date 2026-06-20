<?php

namespace App\Actions\Admin\Order;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class GetAdminOrdersAction
{
    public function execute(): LengthAwarePaginator
    {
        return Order::with([
            'customer:id,phone', 
            'customer.profile:customer_id,first_name,last_name',
            'branch:id,name',
            'driver' => fn($q) => $q->select('id'),
            'driver.profile' => fn($q) => $q->select('driver_id', 'first_name', 'last_name')
        ])
            // RECTIFICACIÓN: Excluimos estados finales según la migración
            ->whereNotIn('status', ['expired', 'cancelled', 'delivered', 'returned']) 
            ->orderByRaw("
                CASE 
                    WHEN status = 'payment_pending' THEN 1 -- RECTIFICADO
                    WHEN status = 'preparing' THEN 2
                    WHEN status = 'confirmed' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'asc') 
            ->paginate(20);
    }
}