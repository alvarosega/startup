<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    public function creating(Product $product): void
    {
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
        }
    }

    public function updated(Product $product): void
    {
        // Limpiar imagen vieja si se cambia
        if ($product->isDirty('image_path')) {
            $original = $product->getOriginal('image_path');
            if ($original && Storage::disk('public')->exists($original)) {
                Storage::disk('public')->delete($original);
            }
        }

        // Cascada: Si desactivas el producto, desactivas los SKUs (opcional pero recomendado)
        if ($product->isDirty('is_active') && !$product->is_active) {
            $product->skus()->update(['is_active' => false]);
        }
    }

    public function forceDeleted(Product $product): void
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
    }
}