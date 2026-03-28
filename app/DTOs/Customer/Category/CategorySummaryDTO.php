<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Category;

readonly class CategorySummaryDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $image_path,
        public ?string $bg_color // AÑADIDO
    ) {}
}