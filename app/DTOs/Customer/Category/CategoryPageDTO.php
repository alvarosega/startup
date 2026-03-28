<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Category;

readonly class CategoryPageDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $image_path,
        public ?string $bg_color, // AÑADIDO
        public array $seo,
        /** @var CreativeDTO[] */
        public array $banners,
        /** @var CategorySummaryDTO[] */
        public array $subcategories
    ) {}
}