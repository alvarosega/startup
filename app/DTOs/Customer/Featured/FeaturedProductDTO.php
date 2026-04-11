<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Featured;

/**
 * PROTOCOLO MILITAR: Inmutabilidad garantizada.
 */
final readonly class FeaturedProductDTO
{
    public string $image_url;

    public function __construct(
        public string $id,           // RECTIFICACIÓN: Atributo añadido
        public string $name,
        public string $slug,
        ?string $image_path,
        public string $brand_name,
        public bool $is_out_of_stock
    ) {
        $this->image_url = $image_path 
            ? asset('storage/' . $image_path) 
            : asset('assets/img/product_placeholder.png');
    }
}