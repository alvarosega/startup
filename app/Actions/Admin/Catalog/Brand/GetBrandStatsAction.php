<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;

class GetBrandStatsAction
{
    public function execute(): array
    {
        return [
            'total'    => Brand::count(),
            'active'   => Brand::where('is_active', true)->count(),
            'featured' => Brand::where('is_featured', true)->count(),
        ];
    }
}