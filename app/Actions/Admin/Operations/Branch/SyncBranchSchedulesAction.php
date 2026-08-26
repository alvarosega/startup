<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Branch;

use App\Models\Operations\Branch;
use App\Models\Operations\BranchSchedule;
use Illuminate\Support\Facades\DB;

class SyncBranchSchedulesAction
{
    public function execute(Branch $branch, array$schedules): void
    {
        DB::transaction(function () use ($branch,$schedules) {
            foreach ($schedules as$item) {
                BranchSchedule::updateOrCreate(
                    [
                        'branch_id'   => $branch->id,
                        'day_of_week' => $item['day_of_week'],
                    ],
                    [
                        'opening_time' => $item['is_closed'] ? '00:00:00' :$item['opening_time'],
                        'closing_time' => $item['is_closed'] ? '00:00:00' :$item['closing_time'],
                        'is_closed'    => (bool) $item['is_closed'],
                    ]
                );
            }
        });
    }
}