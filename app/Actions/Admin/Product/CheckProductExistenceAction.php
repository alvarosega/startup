<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;

class CheckProductExistenceAction
{
    public function execute(?string $name): bool
    {
        // Blindaje contra nulos o strings vacíos
        if (empty(trim($name))) {
            return false;
        }

        return Product::where('name', trim($name))->exists();
    }
}