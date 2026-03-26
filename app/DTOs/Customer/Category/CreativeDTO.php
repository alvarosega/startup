<?php

declare(strict_types=1);

namespace App\DTOs\Customer\Category;

readonly class CreativeDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $image_url,
        public string $action_type,
        public array $target_data,
        public int $sort_order
    ) {}
}