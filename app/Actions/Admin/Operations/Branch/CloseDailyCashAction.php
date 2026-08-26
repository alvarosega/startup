<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use App\Models\Operations\BranchDailyCash;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CloseDailyCashAction
{
    public function execute(Branch $branch, string$adminId, float $actualAmount, ?string $closingNotes): BranchDailyCash
    {
        /** @var BranchDailyCash|null $cash */
        $cash =$branch->dailyCashes()->where('status', 'open')->latest('opened_at')->first();

        if (!$cash) {
            throw ValidationException::withMessages([
                'actual_amount' => 'No existe una caja abierta para cerrar en esta sucursal.',
            ]);
        }

        $expected = (float)$cash->expected_amount;
        $difference = round($actualAmount -$expected, 2);

        $cash->update([
            'closed_by_admin_id' => $adminId,
            'actual_amount'      => $actualAmount,
            'difference'         => $difference,
            'status'             => 'closed',
            'closing_notes'      => $closingNotes,
            'closed_at'          => Carbon::now(),
        ]);

        return $cash;
    }
}