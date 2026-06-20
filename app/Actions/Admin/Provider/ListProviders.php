<?php
namespace App\Actions\Admin\Provider;
use Illuminate\Contracts\Pagination\CursorPaginator;
use App\Models\Provider;


class ListProviders
{
    public function execute(?string $search = null): CursorPaginator
    {
        return Provider::query()
            ->when($search, fn($q) => $q->where(fn($sub) => 
                $sub->where('company_name', 'like', "%{$search}%")
                    ->orWhere('commercial_name', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%")
                    ->orWhere('internal_code', 'like', "%{$search}%")
            ))
            ->orderBy('company_name', 'asc')
            ->cursorPaginate(15); // Optimización Hostinger: Elimina el COUNT(*) masivo.
    }
}