<?php

namespace App\DTOs\Customer\Product;

readonly class ProductSummaryDTO
{
    public string $name;
    public string $slug;
    public ?string $image_url;
    public bool $is_out_of_stock;

    public function __construct(
        string $name,
        string $slug,
        ?string $image_path,
        bool $is_out_of_stock = false
    ) {
        $this->name = mb_convert_encoding(trim($name), 'UTF-8', 'UTF-8');
        $this->slug = strtolower(trim($slug));
        $this->image_url = $image_path 
            ? asset('storage/' . $image_path) 
            : asset('assets/img/product_placeholder.png');
        $this->is_out_of_stock = $is_out_of_stock;
    }
}