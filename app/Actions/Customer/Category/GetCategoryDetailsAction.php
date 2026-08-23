<?php

declare(strict_types=1);

namespace App\Actions\Customer\Category;

use App\DTOs\Customer\Category\CategoryDetailsData;
use Illuminate\Support\Facades\DB;
use DomainException;

final class GetCategoryDetailsAction
{
    public function execute(string $slug): CategoryDetailsData
    {
        /** @var array<string, mixed>|null $category */
        $category = DB::table('categories')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('deleted_epoch', 0)
            ->first(['id', 'name', 'slug', 'requires_age_check', 'bg_color']);

        if ($category === null) {
            throw new DomainException("La categoría solicitada ['{$slug}'] no existe o no se encuentra activa.");
        }

        return CategoryDetailsData::fromArray((array) $category);
    }
}