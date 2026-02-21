<?php   
namespace App\DTOs\Admin\Product;

use App\DTOs\Admin\Sku\UpdateSkuDTO; // Importante
use Illuminate\Http\UploadedFile;

readonly class UpdateProductDTO
{
    public function __construct(
        public string $name,
        public string $brandId,
        public string $categoryId,
        public ?string $description,
        public bool $isActive,
        public bool $isAlcoholic,
        public ?UploadedFile $image,
        /** @var UpdateSkuDTO[] */
        public array $skus 
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            brandId: $request->validated('brand_id'),
            categoryId: $request->validated('category_id'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', true),
            isAlcoholic: (bool) $request->validated('is_alcoholic', false),
            image: $request->file('image'),
            skus: array_map(
                fn($skuArray) => UpdateSkuDTO::fromArray($skuArray), 
                $request->validated('skus', [])
            )
        );
    }
}