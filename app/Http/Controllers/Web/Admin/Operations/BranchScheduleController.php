
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Actions\Admin\Operations\Branch\SyncBranchSchedulesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\Branch\UpdateBranchSchedulesRequest;
use App\Models\Operations\Branch;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchScheduleController extends Controller
{
    public function index(Branch $branch): Response
    {
        $existing = $branch->schedules()->orderBy('day_of_week')->get()->keyBy('day_of_week');

        $schedules = collect(range(0, 6))->map(function (int $day) use ($existing): array {
            if ($existing->has($day)) {
                $item = $existing->get($day);
                return [
                    'day_of_week'  => (int) $item->day_of_week,
                    'opening_time' => $item->opening_time ? substr((string) $item->opening_time, 0, 5) : '08:00',
                    'closing_time' => $item->closing_time ? substr((string) $item->closing_time, 0, 5) : '22:00',
                    'is_closed'    => (bool) $item->is_closed,
                ];
            }

            return [
                'day_of_week'  => $day,
                'opening_time' => '08:00',
                'closing_time' => '22:00',
                'is_closed'    => false,
            ];
        });

        return Inertia::render('Admin/Operations/Branches/Schedules/Index', [
            'branch'    => $branch->only(['id', 'name', 'slug']),
            'schedules' => $schedules->values()->all(),
        ]);
    }

    public function update(
        UpdateBranchSchedulesRequest $request,
        Branch $branch,
        SyncBranchSchedulesAction $action
    ): RedirectResponse {
        $action->execute($branch,$request->validated()['schedules']);

        return redirect()->back()->with('success', 'Matriz de horarios semanales actualizada.');
    }
}