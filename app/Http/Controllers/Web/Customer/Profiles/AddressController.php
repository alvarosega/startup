<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Actions\Customer\Auth\GetActiveBranchesAction;
use App\Actions\Customer\Auth\GetCollidingBranchesAction;
use App\Actions\Customer\Profiles\UpsertAddressAction;
use App\Actions\Customer\Profiles\SetDefaultAddressAction;
use App\Actions\Customer\Profiles\DeleteAddressAction;
use App\DTOs\Customer\Profiles\AddressData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\AddressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class AddressController extends Controller
{
    public function index(GetActiveBranchesAction $branchAction): Response
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        
        $addresses = $user->addresses()
            ->where('deleted_epoch', 0)
            ->orderBy('is_default', 'desc')
            ->get()
            ->map(fn($addr) => [
                'id' => $addr->id,
                'alias' => $addr->alias,
                'address' => $addr->address,
                'reference' => $addr->reference,
                'is_default' => (bool) $addr->is_default,
                'branch_id' => $addr->branch_id
            ])->toArray();

        $activeBranches = $branchAction->execute();
        
        return Inertia::render('Customer/Profiles/AddressesPage', [
            'addresses' => $addresses,
            'activeBranches' => $activeBranches->data
        ]);
    }

    public function create(Request $request, GetActiveBranchesAction $branchAction, GetCollidingBranchesAction $collidingAction): Response|RedirectResponse
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        
        if ($user->addresses()->where('deleted_epoch', 0)->count() >= 10) {
            return redirect()->route('customer.profile.addresses')
                ->withErrors(['limit' => 'Límite de 10 direcciones alcanzado. Elimine una ubicación para continuar.']);
        }

        $lat = $request->filled('latitude') ? (float) $request->query('latitude') : null;
        $lng = $request->filled('longitude') ? (float) $request->query('longitude') : null;

        $activeBranches = $branchAction->execute();
        $collidingBranches = $collidingAction->execute($lat, $lng);

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address' => null, 
            'activeBranches' => $activeBranches->data,
            'collidingBranches' => $collidingBranches->data
        ]);
    }

    public function store(AddressRequest $request, UpsertAddressAction $action): RedirectResponse
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();

        $dto = AddressData::fromArray($request->validated());
        $action->execute($user, $dto);

        return redirect()->route('customer.profile.addresses')
            ->with('success', 'Ubicación guardada de forma exitosa.');
    }

    public function edit(string $id, Request $request, GetActiveBranchesAction $branchAction, GetCollidingBranchesAction $collidingAction): Response
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        $addressModel = $user->addresses()->where('deleted_epoch', 0)->findOrFail($id);

        $lat = $request->filled('latitude') ? (float) $request->query('latitude') : null;
        $lng = $request->filled('longitude') ? (float) $request->query('longitude') : null;

        $activeBranches = $branchAction->execute();
        $collidingBranches = $collidingAction->execute($lat, $lng);

        $address = [
            'id' => $addressModel->id,
            'alias' => $addressModel->alias,
            'address' => $addressModel->address,
            'reference' => $addressModel->reference,
            'branch_id' => $addressModel->branch_id
        ];

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address' => $address, 
            'activeBranches' => $activeBranches->data,
            'collidingBranches' => $collidingBranches->data
        ]);
    }

    public function update(string $id, AddressRequest $request, UpsertAddressAction $action): RedirectResponse
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();

        $dto = AddressData::fromArray($request->validated());
        $action->execute($user, $dto, $id);

        return redirect()->route('customer.profile.addresses')
            ->with('success', 'Ubicación modificada de forma exitosa.');
    }

    public function makeDefault(string $id, SetDefaultAddressAction $action): RedirectResponse
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();

        $action->execute($user, $id);

        return redirect()->route('customer.profile.addresses')
            ->with('success', 'Dirección preferida actualizada.');
    }

    public function destroy(string $id, DeleteAddressAction $action): RedirectResponse
    {
        /** @var \App\Models\Users\Customer $user */
        $user = Auth::guard('customer')->user();
        
        $result = $action->execute($user, $id);

        if (!$result->isSuccess) {
            return back()->withErrors(['delete' => $result->errorMessage]);
        }

        return redirect()->route('customer.profile.addresses')
            ->with('success', 'Dirección removida de la libreta.');
    }
}