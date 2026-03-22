<?php

namespace App\Actions\Admin\RetailMedia;

use App\Models\Sku;
use Illuminate\Support\Collection;

class SearchSkusAction
{
    public function execute(?string $term): \Illuminate\Support\Collection
    {
        if (!$term || strlen($term) < 2) return collect();
    
        return Sku::query()
            ->with('product:id,name')
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            })
            ->limit(15)
            ->get(['id', 'name', 'code']);
    }
}