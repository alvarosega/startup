<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;

class CheckProductExistenceAction
{
    public function execute(string $name): bool
    {
        return Product::where('name', $name)
            ->whereNull('deleted_at')
            ->exists();
    }
}