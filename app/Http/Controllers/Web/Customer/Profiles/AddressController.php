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
use App\Services\Customer\Queries\CustomerQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class AddressController extends Controller
{
    public function __construct(
        private readonly CustomerQueryService $customerQueryService
    ) {}

    public function index(GetActiveBranchesAction $branchAction): Response
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();
        
        $activeBranches = $branchAction->execute();
        
        return Inertia::render('Customer/Profiles/AddressesPage', [
            'addresses'      => $this->customerQueryService->getAddressesForCustomer($customerId),
            'activeBranches' => $activeBranches->data,
        ]);
    }

    public function create(
        Request $request, 
        GetActiveBranchesAction $branchAction, 
        GetCollidingBranchesAction $collidingAction
    ): Response {
        $lat = $request->filled('latitude') ? (float) $request->query('latitude') : null;
        $lng = $request->filled('longitude') ? (float) $request->query('longitude') : null;

        $activeBranches = $branchAction->execute();
        $collidingBranches = $collidingAction->execute($lat, $lng);

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address'           => null, 
            'activeBranches'    => $activeBranches->data,
            'collidingBranches' => $collidingBranches->data,
        ]);
    }

    public function store(AddressRequest $request, UpsertAddressAction $action): RedirectResponse
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();

        $dto = AddressData::fromArray($request->validated());
        $action->execute($customerId, $dto);

        return redirect()->route('customer.profile.addresses.index')
            ->with('success', 'Ubicación guardada de forma exitosa.');
    }

    public function edit(
        string $id, 
        Request $request, 
        GetActiveBranchesAction $branchAction, 
        GetCollidingBranchesAction $collidingAction
    ): Response {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();
        
        $address = $this->customerQueryService->getAddressByIdForCustomer($id, $customerId);
        if (!$address) {
            abort(404, 'La dirección solicitada no existe o no pertenece a su cuenta.');
        }

        // Si el request no provee coordenadas de refresco, se inyectan las de la dirección almacenada
        $lat = $request->filled('latitude') ? (float) $request->query('latitude') : $address['latitude'];
        $lng = $request->filled('longitude') ? (float) $request->query('longitude') : $address['longitude'];

        $activeBranches = $branchAction->execute();
        $collidingBranches = $collidingAction->execute($lat, $lng);

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address'           => $address, 
            'activeBranches'    => $activeBranches->data,
            'collidingBranches' => $collidingBranches->data,
        ]);
    }

    public function update(string $id, AddressRequest $request, UpsertAddressAction $action): RedirectResponse
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();

        $dto = AddressData::fromArray($request->validated());
        $action->execute($customerId, $dto, $id);

        return redirect()->route('customer.profile.addresses.index')
            ->with('success', 'Ubicación modificada de forma exitosa.');
    }

    public function makeDefault(string $id, SetDefaultAddressAction $action): RedirectResponse
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();

        $action->execute($customerId, $id);

        return redirect()->route('customer.profile.addresses.index')
            ->with('success', 'Dirección preferida actualizada.');
    }

    public function destroy(string $id, DeleteAddressAction $action): RedirectResponse
    {
        /** @var string $customerId */
        $customerId = Auth::guard('customer')->id();
        
        $result = $action->execute($customerId, $id);

        if (!$result->isSuccess) {
            return back()->withErrors(['delete' => $result->errorMessage]);
        }

        return redirect()->route('customer.profile.addresses.index')
            ->with('success', 'Dirección removida de la libreta.');
    }
}