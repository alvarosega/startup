<?php

namespace App\Actions\Admin\Shared;

use Illuminate\Support\Facades\DB;

final class ReorderEntityAction
{
    /**
     * Pilar 3.A: Rendimiento Extremo.
     * Actualiza múltiples registros en una sola transacción SQL usando CASE.
     */
    public function execute(string $table, array $orderedIds): void
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

            // Una sola query para N actualizaciones
            DB::statement("UPDATE {$table} SET sort_order = (CASE {$casesString} END) WHERE id IN ({$idsString})");
        });
    }
}