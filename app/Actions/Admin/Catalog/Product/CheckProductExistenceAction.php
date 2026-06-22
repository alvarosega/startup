<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;

class CheckProductExistenceAction
{
    public function execute(?string $name): bool
    {
        $cleanName = trim($name ?? '');
        if (strlen($cleanName) < 3) {
            return false;
        }

        return Product::where('name', $cleanName)
            ->where('deleted_epoch', 0)
            ->exists();
    }
}