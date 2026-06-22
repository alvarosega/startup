<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;

class GetProductFormDataAction
{
    public function execute(): array
    {
        return [
            'brands'     => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('is_active', true)->whereNull('parent_id')->orderBy('name')->get(['id', 'name']),
        ];
    }
}