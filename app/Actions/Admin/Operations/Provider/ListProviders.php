<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Provider;

use App\Models\Operations\Provider;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ListProviders
{
    public function execute(?string $search = null): CursorPaginator
    {
        return Provider::query()
            ->when($search, function ($query, $search) {
                $term = "{$search}%"; // Uso exclusivo de sufijo para optimizar y aprovechar los índices B-Tree en MySQL 8.0
                $query->where(function ($sub) use ($term) {
                    $sub->where('company_name', 'like', $term)
                        ->orWhere('commercial_name', 'like', $term)
                        ->orWhere('tax_id', 'like', $term)
                        ->orWhere('internal_code', 'like', $term);
                });
            })
            ->orderBy('company_name', 'asc')
            ->cursorPaginate(15);
    }
}