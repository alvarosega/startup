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
            // CRÍTICO: Cargar Driver y su Perfil para evitar el error RelationNotFound
            'driver' => function($query) {
                $query->select('id');
            },
            'driver.profile' => function($query) {
                $query->select('driver_id', 'first_name', 'last_name');
            }
        ])
            ->whereNotIn('status', ['expired', 'cancelled', 'completed']) 
            ->orderByRaw("
                CASE 
                    WHEN status = 'under_review' THEN 1
                    WHEN status = 'preparing' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'asc') 
            ->paginate(20);
    }
}