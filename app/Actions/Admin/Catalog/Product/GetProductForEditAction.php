<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Product;

class GetProductForEditAction
{
    /**
     * RECTIFICACIÓN: Mapea jerárquicamente un modelo cargado a estructuras planas,
     * reemplazando de forma incondicional el uso de transformadores REST.
     */
    public function execute(Product $product): array
    {
        $product->load(['skus.prices', 'brand', 'category']);

        return [
            'id'           => (string) $product->id,
            'brand_id'     => (string) $product->brand_id,
            'category_id'  => (string) $product->category_id,
            'name'         => (string) $product->name,
            'slug'         => (string) $product->slug,
            'description'  => $product->description ? (string) $product->description : null,
            'image_path'   => $product->image_path ? (string) $product->image_path : null,
            'is_active'    => (bool) $product->is_active,
            'is_featured'  => (bool) $product->is_featured,
            'is_alcoholic' => (bool) $product->is_alcoholic,
            'sort_order'   => (int) $product->sort_order,
            'brand'        => $product->brand ? [
                'id'   => (string) $product->brand->id,
                'name' => (string) $product->brand->name
            ] : null,
            'category'     => $product->category ? [
                'id'   => (string) $product->category->id,
                'name' => (string) $product->category->name
            ] : null,
            'skus'         => $product->skus->map(fn($sku) => [
                'id'                => (string) $sku->id,
                'name'              => (string) $sku->name,
                'code'              => (string) $sku->code,
                'base_price'        => (float) $sku->base_price,
                'conversion_factor' => (float) $sku->conversion_factor,
                'weight'            => (float) $sku->weight,
                'image_path'        => $sku->image_path ? (string) $sku->image_path : null,
                'is_active'         => (bool) $sku->is_active,
                'sort_order'        => (int) $sku->sort_order,
                'prices'            => $sku->prices->map(fn($p) => [
                    'id'          => (string) $p->id,
                    'final_price' => (float) $p->final_price,
                    'list_price'  => (float) $p->list_price,
                ])->toArray()
            ])->toArray()
        ];
    }
}