<?php
namespace App\DTOs\Customer\Product;

readonly class ProductShowcaseDTO
{
    public string $name;
    public string $slug;
    public ?string $description;
    public string $image_url;
    public array $skus;
    public array $others; // Array de ProductSummaryDTO
    public ?string $next_cursor;

    public function __construct(
        string $name,
        string $slug,
        ?string $description,
        ?string $image_path,
        array $skus,
        array $others,
        ?string $next_cursor
    ) {
        $this->name = mb_convert_encoding(trim($name), 'UTF-8', 'UTF-8');
        $this->slug = $slug;
        $this->description = $description;
        // NORMALIZACIÓN DE PLACEHOLDER: Aquí se soluciona el error
        $this->image_url = $image_path 
            ? asset('storage/' . $image_path) 
            : asset('assets/img/product_placeholder.png');
        $this->skus = $skus;
        $this->others = $others;
        $this->next_cursor = $next_cursor;
    }
}