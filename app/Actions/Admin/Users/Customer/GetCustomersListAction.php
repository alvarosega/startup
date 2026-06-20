<?php

namespace App\Actions\Admin\Users\Customer;

use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Support\Facades\Cache;

class GetCustomersListAction
{
    public function execute(array $filters): array
    {
        $page = request('page', 1);
        $isFiltered = !empty($filters['search']) || !empty($filters['branch_id']);
        
        // Cacheamos solo la vista base (sin filtros y pagina 1) para velocidad extrema
        if (!$isFiltered && $page == 1) {
            $users = Cache::remember('admin_customers_list_base', 86400, fn() => $this->buildQuery($filters));
        } else {
            $users = $this->buildQuery($filters);
        }

        return [
            'users'    => $users,
            'branches' => Branch::all(['id', 'name']),
            'filters'  => $filters 
        ];
    }

    private function buildQuery(array $filters)
    {
        $query = Customer::query()
            ->select([
                'customers.id', 
                'customer_profiles.first_name', 
                'customer_profiles.last_name', 
                'customer_profiles.avatar_type',   
                'customer_profiles.avatar_source', 
                'customers.email', 
                'customers.phone', 
                'customers.is_active', 
                'customers.created_at', 
                'branches.name as branch_name'
            ])
            ->leftJoin('customer_profiles', 'customers.id', '=', 'customer_profiles.customer_id')
            ->leftJoin('branches', 'customers.branch_id', '=', 'branches.id');

        if (!empty($filters['search'])) {
            $s = "%{$filters['search']}%";
            $query->where(function($q) use ($s) {
                $q->where('customers.email', 'like', $s)
                  ->orWhere('customers.phone', 'like', $s)
                  ->orWhere('customer_profiles.first_name', 'like', $s)
                  ->orWhere('customer_profiles.last_name', 'like', $s);
            });
        }

        if (!empty($filters['branch_id'])) {
            $query->where('customers.branch_id', $filters['branch_id']);
        }

        return $query->orderBy('customers.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
    }
}