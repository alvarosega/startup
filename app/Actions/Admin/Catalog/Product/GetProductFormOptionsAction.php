<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Product;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Operations\Branch;

class GetProductFormOptionsAction
{
    /**
     * RECTIFICACIÓN: Centraliza de forma unificada la hidratación de catálogos cruzados
     * eliminando las fugas de consultas crudas que operaban dentro del controlador.
     */
    public function execute(): array
    {
        return [
            'brands'     => Brand::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($b) => ['id' => (string) $b->id, 'name' => (string) $b->name])
                ->toArray(),

            'categories' => Category::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($c) => ['id' => (string) $c->id, 'name' => (string) $c->name])
                ->toArray(),

            'branches'   => Branch::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($br) => ['id' => (string) $br->id, 'name' => (string) $br->name])
                ->toArray(),
        ];
    }
}