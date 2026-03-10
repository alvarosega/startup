<?php

namespace App\Actions\Admin\Product;

use App\Models\{Brand, Category};

class GetProductFormDataAction
{
    public function execute(): array
    {
        return [
            // Extraemos solo los datos planos y activos
            'brands'     => Brand::active()->get(['id', 'name']),
            'categories' => Category::active()->get(['id', 'name']),
        ];
    }
}