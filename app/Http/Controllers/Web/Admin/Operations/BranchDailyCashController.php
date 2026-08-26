<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Actions\Admin\Operations\Branch\CloseDailyCashAction;
use App\Actions\Admin\Operations\Branch\OpenDailyCashAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Operations\Branch\CloseDailyCashRequest;
use App\Http\Requests\Admin\Operations\Branch\OpenDailyCashRequest;
use App\Models\Operations\Branch;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BranchDailyCashController extends Controller
{
    public function index(Branch $branch): Response
    {
        $currentCash =$branch->dailyCashes()
            ->with(['openedBy:id,first_name,last_name', 'closedBy:id,first_name,last_name'])
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        $history =$branch->dailyCashes()
            ->with(['openedBy:id,first_name,last_name', 'closedBy:id,first_name,last_name'])
            ->orderBy('date', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Operations/Branches/Cash/Index', [
            'branch'      => $branch->only(['id', 'name', 'slug']),
            'currentCash' => $currentCash,
            'history'     => $history,
        ]);
    }

    public function open(OpenDailyCashRequest $request, Branch $branch, OpenDailyCashAction$action): RedirectResponse
    {
        $action->execute($branch, (string) $request->user('super_admin')->id, (float)$request->validated()['opening_amount']);

        return redirect()->back()->with('success', 'Caja diaria aperturada formalmente.');
    }

    public function close(CloseDailyCashRequest $request, Branch $branch, CloseDailyCashAction$action): RedirectResponse
    {
        $validated =$request->validated();
        $action->execute($branch,
            (string) $request->user('super_admin')->id,
            (float) $validated['actual_amount'],$validated['closing_notes'] ?? null
        );

        return redirect()->back()->with('success', 'Arqueo y cierre de caja procesado correctamente.');
    }
}