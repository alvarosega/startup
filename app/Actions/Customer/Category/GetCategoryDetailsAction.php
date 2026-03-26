<?php

declare(strict_types=1);

namespace App\Actions\Customer\Category;

use App\Models\Category;
use App\Models\AdCreative;
use App\Services\ShopContextService;
use App\DTOs\Customer\Category\{CategoryPageDTO, CategorySummaryDTO, CreativeDTO};

class GetCategoryDetailsAction
{
    public function __construct(
        private ShopContextService $shopContext
    ) {}

    public function execute(string $slug, string $deviceType): CategoryPageDTO
    {
        // CORRECCIÓN: Invocación de instancia (->), no estática (::)
        $branchId = $this->shopContext->getActiveBranchId();

        // 1. Resolución de Categoría (Base)
        $category = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug', 'description', 'image_path', 'seo_title', 'seo_description'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // 2. Carga de Subcategorías (Nivel 1 de profundidad)
        $subcategories = Category::query()
            ->select(['id', 'name', 'slug', 'image_path'])
            ->where('parent_id', $category->id)
            ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($sub) => new CategorySummaryDTO(
                id: (string) $sub->id,
                name: $sub->name,
                slug: $sub->slug,
                image_path: $sub->image_path
            ))
            ->toArray();

        // 3. Resolución de Banners con Herencia (Fallback al Padre)
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
            seo: [
                'title' => $category->seo_title ?? $category->name,
                'description' => $category->seo_description
            ],
            banners: $banners,
            subcategories: $subcategories
        );
    }

    private function resolveBanners(string $categoryId, string $branchId, string $deviceType): array
    {
        $imageColumn = ($deviceType === 'desktop') ? 'image_desktop_path' : 'image_mobile_path';

        return AdCreative::query()
            ->select([
                'ad_creatives.id', 
                'ad_creatives.name', 
                "ad_creatives.{$imageColumn} as path", 
                'ad_creatives.action_type', 
                'ad_creatives.target_id', 
                'ad_creatives.target_type',
                'ad_creatives.sort_order'
            ])
            ->join('ad_campaigns', 'ad_creatives.campaign_id', '=', 'ad_campaigns.id')
            ->where('ad_creatives.category_id', $categoryId)
            ->where('ad_creatives.branch_id', $branchId)
            ->where('ad_creatives.is_active', true)
            ->where('ad_campaigns.is_active', true)
            ->where(function ($query) {
                $now = now();
                $query->whereNull('ad_campaigns.starts_at')
                      ->orWhere('ad_campaigns.starts_at', '<=', $now);
            })
            ->where(function ($query) {
                $now = now();
                $query->whereNull('ad_campaigns.ends_at')
                      ->orWhere('ad_campaigns.ends_at', '>=', $now);
            })
            ->orderBy('ad_creatives.sort_order', 'asc')
            ->get()
            ->map(fn($banner) => new CreativeDTO(
                id: (string) $banner->id,
                name: $banner->name,
                image_url: $banner->path,
                action_type: $banner->action_type,
                target_data: [
                    'id' => (string) $banner->target_id,
                    'type' => $banner->target_type
                ],
                sort_order: (int) $banner->sort_order
            ))
            ->toArray();
    }
}