<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Actions\Customer\Auth\GetActiveBranchesAction;
use App\Actions\Customer\Auth\GetCollidingBranchesAction;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisterController extends Controller
{
    public function create(
        Request $request, 
        GetActiveBranchesAction $branchesAction,
        GetCollidingBranchesAction $collidingAction
    ): Response {
        $lat = $request->filled('latitude') ? (float) $request->query('latitude') : null;
        $lng = $request->filled('longitude') ? (float) $request->query('longitude') : null;

        $activeBranches = $branchesAction->execute();
        $collidingBranches = $collidingAction->execute($lat, $lng);
        
        $avatars = collect(range(1, 8))->map(fn (int $i) => [
            'id' => "avatar_{$i}.png",
            'url' => asset("assets/avatars/avatar_{$i}.png")
        ])->toArray();

        return Inertia::render('Customer/Auth/Register', [
            'activeBranches' => $activeBranches->data,
            'collidingBranches' => $collidingBranches->data,
            'availableAvatars' => $avatars
        ]);
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $validated['guest_client_uuid'] = $request->session()->get('guest_client_uuid');

        $dto = RegisterCustomerData::fromArray($validated);
        
        $idempotencyKey = $request->header('X-Idempotency-Key');
        $idempotencyKey = is_array($idempotencyKey) ? ($idempotencyKey[0] ?? null) : $idempotencyKey;

        $result = $action->execute($dto, $idempotencyKey);

        if (!$result->isSuccess) {
            return back()->withErrors(['phone' => $result->errorMessage])->withInput();
        }

        /** @var \Illuminate\Contracts\Auth\Authenticatable $customer */
        $customer = $result->data;
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.index')
            ->with('status', 'Registro completado con éxito.');
    }
}