<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Customer\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profiles\AddressRequest;
use App\DTOs\Customer\Profiles\AddressData;
use App\Actions\Customer\Auth\GetActiveBranchesAction; 
use App\Actions\Customer\Profiles\UpsertAddressAction;
use App\Actions\Customer\Profiles\SetDefaultAddressAction;
use App\Http\Resources\Customer\Branch\BranchResource; 
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    public function __construct(
        protected readonly UpsertAddressAction $upsertAction,
        protected readonly SetDefaultAddressAction $setDefaultAction
    ) {}

    /**
     * Lista de direcciones activas del cliente.
     */
    public function index(GetActiveBranchesAction $branchAction): Response
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();
        
        return Inertia::render('Customer/Profiles/AddressesPage', [
            'addresses'      => $user->addresses()->orderBy('is_default', 'desc')->get(),
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    /**
     * Renderiza el formulario de creación controlando el límite de la cuenta.
     */
    public function create(GetActiveBranchesAction $branchAction): Response|RedirectResponse
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();
        
        if ($user->addresses()->count() >= 10) {
            return redirect()->route('customer.profile.addresses')
                ->withErrors(['limit' => 'Límite de 10 direcciones alcanzado. Elimine una ubicación para continuar.']);
        }

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address'        => null, 
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    /**
     * Procesa e inserta una nueva ubicación a través del servicio transaccional.
     */
    public function store(AddressRequest $request): RedirectResponse
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();

        try {
            $this->upsertAction->execute(
                $user,
                AddressData::fromRequest($request)
            );

            return redirect()->route('customer.profile.addresses')
                ->with('success', 'Ubicación guardada de forma exitosa.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Vista de edición de una dirección específica.
     */
    public function edit(string $id, GetActiveBranchesAction $branchAction): Response
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();
        $address = $user->addresses()->findOrFail($id);

        return Inertia::render('Customer/Profiles/AddressFormPage', [
            'address'        => $address, 
            'activeBranches' => BranchResource::collection($branchAction->execute())->resolve()
        ]);
    }

    /**
     * Actualiza una ubicación existente delegando el cómputo logístico al Action.
     */
    public function update(string $id, AddressRequest $request): RedirectResponse
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();

        try {
            $this->upsertAction->execute(
                $user,
                AddressData::fromRequest($request),
                $id
            );

            return redirect()->route('customer.profile.addresses')
                ->with('success', 'Ubicación modificada de forma exitosa.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Intercambia el puntero de la dirección predeterminada por ID.
     */
    public function makeDefault(string $id): RedirectResponse
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();

        $this->setDefaultAction->execute($user, $id);

        return redirect()->route('customer.profile.addresses')
            ->with('success', 'Dirección preferida actualizada.');
    }

    /**
     * Ejecuta el Soft Delete de una ubicación protegiendo la dirección por defecto.
     */
    public function destroy(string $id): RedirectResponse
    {
        /** @var Customer $user */
        $user = Auth::guard('customer')->user();
        
        $address = $user->addresses()->findOrFail($id);

        if ($address->is_default) {
            return back()->withErrors(['delete' => 'Restricción de Seguridad: No puede eliminar su ubicación predeterminada activa.']);
        }

        // Ejecución de borrado lógico (Soft Delete nativo de Eloquent)
        $address->delete();

        return redirect()->route('customer.profile.addresses')
            ->with('success', 'Dirección removida de la libreta.');
    }
}