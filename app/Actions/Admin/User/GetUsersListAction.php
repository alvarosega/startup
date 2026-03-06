<?php

namespace App\Actions\Admin\User;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\Customer; // <--- AÑADIR ESTA LÍNEA

class GetUsersListAction
{
    public function execute(array $filters): array
    {
        // Busca el método execute y actualiza el select
        $query = Customer::query()
            ->select([
                'customers.id', 
                'customer_profiles.first_name', 
                'customer_profiles.last_name', 
                'customer_profiles.avatar_type',   // <--- NUEVO
                'customer_profiles.avatar_source', // <--- NUEVO
                'customers.email', 
                'customers.phone', 
                'customers.is_active', 
                'customers.created_at', 
                'branches.name as branch_name'
            ])
            ->leftJoin('customer_profiles', 'customers.id', '=', 'customer_profiles.customer_id')
            ->leftJoin('branches', 'customers.branch_id', '=', 'branches.id');
        // Búsqueda con prefijos de tabla para evitar ambigüedad
        if (!empty($filters['search'])) {
            $s = "%{$filters['search']}%";
            $query->where(function($q) use ($s) {
                $q->where('customers.email', 'like', $s)
                  ->orWhere('customers.phone', 'like', $s)
                  ->orWhere('customer_profiles.first_name', 'like', $s)
                  ->orWhere('customer_profiles.last_name', 'like', $s);
            });
        }
    
        // ELIMINADO EL ->through(). Retornamos el paginador con objetos stdClass/Model.
        $users = $query->orderBy('customers.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
    
        return [
            'users'    => $users,
            'branches' => Branch::all(['id', 'name']),
            'filters'  => $filters 
        ];
    }
}