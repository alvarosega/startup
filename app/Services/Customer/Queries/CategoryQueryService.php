<?php

declare(strict_types=1);

namespace App\Services\Customer\Queries;

use Illuminate\Support\Facades\DB;

final class CategoryQueryService
{
    /**
     * Recupera las categorías raíz formateadas directamente para el carrusel de Inertia.
     *
     * @return array<int, array{id: string, name: string, slug: string, image_url: string, bg_color: string}>
     */
    public function getRootCategoriesForCarousel(): array
    {
        return DB::table('categories')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'image_path', 'bg_color'])
            ->map(fn (object $category): array => [
                'id'        => (string) $category->id,
                'name'      => mb_strtoupper((string) $category->name),
                'slug'      => (string) $category->slug,
                'image_url' => $category->image_path ? asset("storage/{$category->image_path}") : '/assets/images/default-category.png',
                'bg_color'  => (string) ($category->bg_color ?? '#32323b'),
            ])
            ->toArray();
    }
}