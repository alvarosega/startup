<?php
namespace App\Actions\Admin\Order;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class GetAdminOrdersAction
{
    public function execute(): LengthAwarePaginator
    {
        // Trae órdenes activas ordenadas por prioridad de acción.
        return Order::with([
            'customer:id,phone', // De customers solo sacamos id y phone
            'customer.profile:customer_id,first_name,last_name', // De profiles sacamos los nombres
            'branch:id,name'
        ])
            ->whereNotIn('status', ['expired', 'cancelled', 'completed']) // Ocultamos historial muerto
            ->orderByRaw("
                CASE 
                    WHEN advance_status = 'under_review' THEN 1
                    WHEN balance_status = 'under_review' THEN 2
                    WHEN status = 'preparing' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'asc') // FEFO logístico (el más antiguo primero)
            ->paginate(20);
    }
}