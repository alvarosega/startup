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
        return Inertia::render('Admin/Operations/Branches/Schedules/Index', [
            'branch'    => $branch->only(['id', 'name', 'slug']),
            'schedules' => $branch->schedules()->orderBy('day_of_week')->get(),
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