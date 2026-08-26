<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\Branch\StoreBranchScheduleExceptionRequest;
use App\Models\Operations\Branch;
use App\Models\Operations\BranchScheduleException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchScheduleExceptionController extends Controller
{
    public function index(Branch $branch): Response
    {
        return Inertia::render('Admin/Operations/Branches/Exceptions/Index', [
            'branch'     => $branch->only(['id', 'name', 'slug']),
            'exceptions' => $branch->scheduleExceptions()
                ->where('date', '>=', now()->toDateString())
                ->orderBy('date', 'asc')
                ->get(),
        ]);
    }

    public function store(StoreBranchScheduleExceptionRequest $request, Branch$branch): RedirectResponse
    {
        $validated =$request->validated();

        $branch->scheduleExceptions()->create([
            'date'         => $validated['date'],
            'opening_time' => $validated['is_closed'] ? null : $validated['opening_time'],
            'closing_time' => $validated['is_closed'] ? null : $validated['closing_time'],
            'is_closed'    => (bool) $validated['is_closed'],
            'reason'       => $validated['reason'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Excepción de horario registrada exitosamente.');
    }

    public function destroy(Branch $branch, BranchScheduleException$exception): RedirectResponse
    {
        if ($exception->branch_id !==$branch->id) {
            abort(403);
        }

        $exception->delete();

        return redirect()->back()->with('success', 'Excepción de horario eliminada.');
    }
}