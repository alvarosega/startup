<?php

namespace App\Actions\Admin\Brand;

use App\Models\Brand;

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