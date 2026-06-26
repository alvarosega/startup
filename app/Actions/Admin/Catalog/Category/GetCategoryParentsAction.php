<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Category;

use App\Models\Catalog\Category;

class GetCategoryParentsAction
{
    /**
     * Recupera y estructura el listado plano de categorías raíz disponibles
     * para popular los selectores jerárquicos del formulario de creación en Vue.
     */
    public function execute(): array
    {
        return Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => [
                'id' => (string) $p->id, 
                'name' => (string) $p->name
            ])
            ->toArray();
    }
}