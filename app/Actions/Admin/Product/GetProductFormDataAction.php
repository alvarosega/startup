<?php

declare(strict_types=1);

namespace App\Actions\Admin\Product;

use App\Models\{Brand, Category};

class GetProductFormDataAction
{
    public function execute(): array
    {
        return [
            'brands'     => Brand::active()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
        ];
    }
}