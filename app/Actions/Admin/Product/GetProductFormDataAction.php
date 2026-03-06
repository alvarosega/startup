<?php

namespace App\Actions\Admin\Product;

use App\Models\{Brand, Category};

class GetProductFormDataAction
{
    public function execute(bool $includeChildren = false): array
    {
        $categoriesQuery = Category::whereNull('parent_id')->active();

        if ($includeChildren) {
            $categoriesQuery->with(['children' => fn($q) => $q->active()]);
        }

        return [
            'brands'     => Brand::active()->get(['id', 'name']),
            'categories' => $categoriesQuery->get(['id', 'name']),
        ];
    }
}