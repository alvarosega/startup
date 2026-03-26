<?php

namespace App\Actions\Admin\Product;

use App\Models\Product;

class CheckProductExistenceAction
{
    public function execute(?string $name): bool
    {
        $cleanName = trim($name ?? '');
        if (strlen($cleanName) < 3) return false;

        // LA LEY: Solo verificamos contra productos VIVOS. 
        // Si uno fue borrado, permitimos reutilizar el nombre.
        return Product::where('name', $cleanName)->exists();
    }
}