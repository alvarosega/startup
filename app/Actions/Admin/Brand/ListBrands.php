<?php
namespace App\Actions\Admin\Brand;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class ListBrands
{
    public function execute(?string $search = null): LengthAwarePaginator
    {
        return Brand::query()
            ->with(['provider:id,company_name,commercial_name', 'category:id,name'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString(); // Mantiene el parámetro 'search' en los links de paginación
    }
}