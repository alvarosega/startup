<?php   
namespace App\DTOs\Customer\Featured;

readonly class ShowcaseDTO
{
    public function __construct(
        public object $product,
        public array $mainSkus,
        public object $othersPaginated
    ) {}
}