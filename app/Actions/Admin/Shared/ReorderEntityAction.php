<?php

declare(strict_types=1);

namespace App\Actions\Admin\Shared;

use Illuminate\Support\Facades\{DB, Cache};

final readonly class ReorderEntityAction
{
    /**
     * Actualización masiva con invalidación de caché.
     * Complejidad: O(1) de red SQL.
     */
    public function execute(string $table, array $orderedIds, ?string $cacheKey = null): void
    {
        if (empty($orderedIds)) return;

        DB::transaction(function () use ($table, $orderedIds) {
            $cases = [];
            $ids = [];
            
            foreach ($orderedIds as $index => $id) {
                $sortOrder = ($index + 1) * 10;
                $cases[] = "WHEN id = '{$id}' THEN {$sortOrder}";
                $ids[] = "'{$id}'";
            }

            $idsString = implode(',', $ids);
            $casesString = implode(' ', $cases);

            // Bloqueo pesimista mediante ejecución directa de UPDATE
            DB::statement("UPDATE {$table} SET sort_order = (CASE {$casesString} END) WHERE id IN ({$idsString})");
        });

        // Pilar 4: Invalidación proactiva
        if ($cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}