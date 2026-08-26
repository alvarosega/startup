<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use App\Models\Operations\BranchDailyCash;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OpenDailyCashAction
{
    public function execute(Branch $branch, string $adminId, float$openingAmount): BranchDailyCash
    {
        $today = Carbon::today()->toDateString();

        if ($branch->dailyCashes()->where('date',$today)->exists()) {
            throw ValidationException::withMessages([
                'opening_amount' => 'La caja de esta sucursal ya fue aperturada para la fecha de hoy.',
            ]);
        }

        return $branch->dailyCashes()->create([
            'opened_by_admin_id' => $adminId,
            'date'               => $today,
            'opening_amount'     => $openingAmount,
            'expected_amount'    => $openingAmount,
            'status'             => 'open',
            'opened_at'          => Carbon::now(),
        ]);
    }
}