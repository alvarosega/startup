<?php

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\AddressRequest;
use App\Http\Resources\Customer\Auth\CustomerResource;
use App\Actions\Customer\Auth\GetActiveBranchesAction; 
use App\Http\Resources\Customer\Branch\BranchResource; 
use App\Services\Geo\BranchCoverageService;
use App\Services\ShopContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function __construct(
        protected BranchCoverageService $geoService,
        protected ShopContextService $shopContext
    ) {}

    public function index(GetActiveBranchesAction $branchAction): Response
    {
        $user = Auth::guard('customer')->user();
        
        return Inertia::render('Customer/Profiles/AddressesPage', [
            'addresses' => $user->addresses()->orderBy('is_default', 'desc')->get(),
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    // CORRECCIÓN: Union Type (Response | RedirectResponse)
    public function create(GetActiveBranchesAction $branchAction): Response|RedirectResponse
    {
        $user = Auth::guard('customer')->user();
        
        if ($user->addresses()->count() >= 5) {
            return redirect()->route('customer.profile.addresses')->withErrors(['limit' => 'Límite de 5 direcciones alcanzado.']);
        }

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address' => null, 
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    public function edit($id, GetActiveBranchesAction $branchAction): Response
    {
        $user = Auth::guard('customer')->user();
        $address = $user->addresses()->findOrFail($id);

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address' => $address, 
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $user = Auth::guard('customer')->user();
        
        if ($user->addresses()->count() >= 5) {
            return back()->withErrors(['limit' => 'Máximo 5 direcciones permitidas.']);
        }

        $assignedBranchId = $this->geoService->identifyBranch(
            $request->latitude, 
            $request->longitude
        ) ?? $this->shopContext->getDefaultBranchId();

        if (!$assignedBranchId) {
            return back()->withErrors(['address' => 'No se pudo asignar una sucursal válida.']);
        }

        $user->addresses()->create([
            'alias'      => $request->alias,
            'address'    => $request->address,
            'reference'  => $request->details,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'branch_id'  => $assignedBranchId,
            'is_default' => $user->addresses()->count() === 0,
        ]);

        // CORRECCIÓN: Redirigir al índice, no volver atrás al formulario
        return redirect()->route('customer.profile.addresses')->with('success', 'Dirección guardada exitosamente.');
    }

    // CORRECCIÓN: Se añade el método update que faltaba
    public function update($id, AddressRequest $request): RedirectResponse
    {
        $user = Auth::guard('customer')->user();
        $address = $user->addresses()->findOrFail($id);

        $assignedBranchId = $this->geoService->identifyBranch(
            $request->latitude, 
            $request->longitude
        ) ?? $this->shopContext->getDefaultBranchId();

        if (!$assignedBranchId) {
            return back()->withErrors(['address' => 'No se pudo asignar una sucursal válida.']);
        }

        $address->update([
            'alias'      => $request->alias,
            'address'    => $request->address,
            'reference'  => $request->details,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'branch_id'  => $assignedBranchId,
        ]);

        // CORRECCIÓN: Redirigir al índice
        return redirect()->route('customer.profile.addresses')->with('success', 'Dirección actualizada exitosamente.');
    }

    public function makeDefault($id): RedirectResponse
    {
        $user = Auth::guard('customer')->user();
        $user->addresses()->update(['is_default' => false]);
        $user->addresses()->where('id', $id)->update(['is_default' => true]);
        return back();
    }

    public function destroy($id): RedirectResponse
    {
        $address = Auth::guard('customer')->user()->addresses()->findOrFail($id);
        if ($address->is_default) {
            return back()->withErrors(['delete' => 'No puedes eliminar la dirección principal.']);
        }
        $address->delete();
        return back();
    }
}