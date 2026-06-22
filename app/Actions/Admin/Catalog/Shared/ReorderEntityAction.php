<?php

declare(strict_types=1);

namespace App\Actions\Admin\Catalog\Shared;

use Illuminate\Support\Facades\{DB, Cache};
use InvalidArgumentException;

final readonly class ReorderEntityAction
{
    public function execute(string $table, array $orderedIds, ?string $cacheKey = null): void
    {
        if (empty($orderedIds)) {
            return;
        }

        // Corrección estricta del patrón de sanitización para nombres de tabla SQL
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException("Estructura de tabla no autorizada.");
        }

        DB::transaction(function () use ($table, $orderedIds) {
            $cases = [];
            $ids = [];
            
            foreach ($orderedIds as $index => $id) {
                if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string) $id)) {
                    throw new InvalidArgumentException("Inyección bloqueada: Formato UUID inválido.");
                }

                $sortOrder = ($index + 1) * 10;
                $cases[] = "WHEN id = '{$id}' THEN {$sortOrder}";
                $ids[] = "'{$id}'";
            }

            $idsString = implode(',', $ids);
            $casesString = implode(' ', $cases);

            DB::statement("UPDATE {$table} SET sort_order = (CASE {$casesString} END) WHERE id IN ({$idsString})");
        });

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}