<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Brand;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Operations\Provider;

class GetBrandFormOptionsAction
{
    /**
     * RECTIFICACIÓN: Desacopla la lógica de agregación del controlador inyectando colecciones mapeadas nativamente.
     */
    public function execute(): array
    {
        return [
            'providers'  => Provider::where('is_active', true)
                ->orderBy('commercial_name')
                ->get(['id', 'commercial_name'])
                ->map(fn($p) => ['id' => (string) $p->id, 'name' => (string) $p->commercial_name])
                ->toArray(),
                
            'categories' => Category::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($c) => ['id' => (string) $c->id, 'name' => (string) $c->name])
                ->toArray(),
                
            'parents'    => Brand::whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($b) => ['id' => (string) $b->id, 'name' => (string) $b->name])
                ->toArray()
        ];
    }
}