<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;

class GetBrandStatsAction
{
    /**
     * Provee indicadores numéricos planos agregados del subdominio en tiempo real.
     */
    public function execute(): array
    {
        return [
            'total'    => (int) Brand::count(),
            'active'   => (int) Brand::where('is_active', true)->count(),
            'featured' => (int) Brand::where('is_featured', true)->count(),
        ];
    }
}