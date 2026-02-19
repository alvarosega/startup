<?php
namespace App\Actions\Admin\Provider;

use App\Models\Provider;
use Illuminate\Pagination\LengthAwarePaginator;

class ListProviders
{
    public function execute(?string $search = null): LengthAwarePaginator
    {
        return Provider::query()
            ->when($search, fn($q) => $q->where(fn($sub) => 
                $sub->where('company_name', 'like', "%{$search}%")
                    ->orWhere('commercial_name', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%")
                    ->orWhere('internal_code', 'like', "%{$search}%")
            ))
            ->orderBy('company_name', 'asc')
            ->paginate(15);
    }
}