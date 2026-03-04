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
            'branch:id,name'
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