<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Category;

readonly class CategorySummaryDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $image_path,
        public ?string $bg_color
    ) {
        $this->name = mb_convert_encoding(trim($name), 'UTF-8', 'UTF-8');
        $this->slug = strtolower(trim($slug));
    }
}