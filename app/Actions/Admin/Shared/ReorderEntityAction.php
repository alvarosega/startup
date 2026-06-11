<?php

declare(strict_types=1);

namespace App\Actions\Admin\Shared;

use Illuminate\Support\Facades\{DB, Cache};
use InvalidArgumentException;

final readonly class ReorderEntityAction
{
    public function execute(string $table, array $orderedIds, ?string $cacheKey = null): void
    {
        if (empty($orderedIds)) return;

        // Validar nombre de la tabla frente a caracteres maliciosos
        if (!preg_match('/^[a-zA- Adele_z0-9_]+$/i', $table)) {
            throw new InvalidArgumentException("Estructura de tabla no autorizada.");
        }

        DB::transaction(function () use ($table, $orderedIds) {
            $cases = [];
            $ids = [];
            
            foreach ($orderedIds as $index => $id) {
                // Sanitización estricta: Validar formato exacto UUID
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