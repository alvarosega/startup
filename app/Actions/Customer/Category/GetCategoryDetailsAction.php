<?php

declare(strict_types=1);

namespace App\Actions\Customer\Category;

use App\Models\{Category, AdCreative};
use App\Services\ShopContextService;
use App\DTOs\Customer\Category\{CategoryPageDTO, CategorySummaryDTO, CreativeDTO};
use Illuminate\Support\Facades\Cache;

class GetCategoryDetailsAction
{
    public function __construct(
        private ShopContextService $shopContext
    ) {}

    /**
     * Lógica para la vista de detalle de categoría
     */
    public function execute(string $slug, string $deviceType): CategoryPageDTO
    {
        $branchId = $this->shopContext->getActiveBranchId();

        $category = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug', 'description', 'image_path', 'bg_color', 'seo_title', 'seo_description'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // --- REEMPLAZAR EL BLOQUE DE $subcategories POR ESTE ---
        $subcategories = Category::query()
            ->select(['id', 'name', 'slug', 'image_path', 'bg_color'])
            ->where('parent_id', $category->id)
            ->where('is_active', true)
            // FILTRO DE CONTEXTO: Solo categorías con productos en esta sucursal
            ->whereHas('products.skus.prices', fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($sub) => new CategorySummaryDTO(
                name: $sub->name,
                slug: $sub->slug,
                image_path: $sub->image_path,
                bg_color: $sub->bg_color
            ))
            ->toArray();

        $banners = $this->resolveBanners($category->id, $branchId, $deviceType);
        
        if (empty($banners) && $category->parent_id) {
            $banners = $this->resolveBanners($category->parent_id, $branchId, $deviceType);
        }

        return new CategoryPageDTO(
            id: (string) $category->id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            image_path: $category->image_path,
            bg_color: $category->bg_color,
            seo: [
                'title' => $category->seo_title ?? $category->name,
                'description' => $category->seo_description
            ],
            banners: $banners,
            subcategories: $subcategories
        );
    }

// ARCHIVO: app/Actions/Customer/Category/GetCategoryDetailsAction.php

    public function getGlobalMenu(string $branchId): array
    {
        $version = cache()->get('admin_categories_version', 1);
        
        return Cache::remember("global_menu_br_{$branchId}_v{$version}", 86400, function () use ($branchId) {
            $categories = Category::query()
                ->select(['name', 'slug', 'image_path', 'bg_color']) // Query Law: Solo lo necesario
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->whereHas('products.skus.prices', fn($q) => $q->where('branch_id', $branchId))
                ->orderBy('sort_order', 'asc')
                ->get();

            // REEMPLAZO CRÍTICO: Devolver DTOs, no Resources
            return $categories->map(fn($cat) => [
                'name' => mb_convert_encoding($cat->name, 'UTF-8', 'UTF-8'),
                'slug' => $cat->slug,
                'image_path' => $cat->image_path,
                'bg_color' => $cat->bg_color,
            ])->toArray();
        });
    }

    private function resolveBanners(string $categoryId, string $branchId, string $deviceType): array
    {
        $imageColumn = ($deviceType === 'desktop') ? 'image_desktop_path' : 'image_mobile_path';

        return AdCreative::query()
            ->where('category_id', $categoryId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('campaign', fn($q) => $q->active()) 
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($banner) => new CreativeDTO(
                id: (string) $banner->id,
                name: $banner->name,
                image_path: $banner->{$imageColumn}, 
                action_type: $banner->action_type,
                target_data: [
                    'id'   => (string) $banner->target_id,
                    'type' => strtolower(class_basename($banner->target_type)) 
                ],
                sort_order: (int) $banner->sort_order
            ))
            ->toArray();
    }
}