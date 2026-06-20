<?php

namespace App\DTOs\Customer\Product;

readonly class SkuShowcaseDTO
{
    public string $name;
    public string $slug;
    public string $brand_name;
    public string $image_url;
    public float $final_price;
    public float $list_price;
    public int $stock;
    public ?array $upsell;

    public function __construct(
        string $name,
        string $slug,
        string $brand_name,
        ?string $image_path,
        float $final_price,
        float $list_price,
        int $stock,
        ?array $upsell = null
    ) {
        $this->name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
        $this->slug = $slug;
        $this->brand_name = $brand_name;
        $this->stock = $stock;
        $this->final_price = $final_price;
        $this->list_price = $list_price;
        $this->upsell = $upsell;
        // SOLUCIÓN AL PLACEHOLDER
        $this->image_url = $image_path 
            ? asset('storage/' . $image_path) 
            : asset('assets/img/product_placeholder.png');
    }
}