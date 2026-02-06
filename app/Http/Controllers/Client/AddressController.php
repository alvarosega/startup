<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\DTOs\Client\AddressData;
use App\Actions\Client\SaveUserAddress;
use App\Services\ShopContextService;

class AddressController extends Controller
{
    public function index()
    {
        return Inertia::render('Profile/Addresses/Index', [
            'addresses' => Auth::user()->addresses()->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function create()
    {
        // Pre-chequeo visual para UX (aunque la Action valida también)
        if (request()->user()->addresses()->count() >= 3) {
            return redirect()->route('profile.index')->with('success', 'Operación exitosa.');
        }

        // Datos para el mapa
        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon', 'latitude', 'longitude')
            ->get();

        return Inertia::render('Profile/Addresses/Create', [
            'activeBranches' => $branches
        ]);
    }

    public function store(Request $request, SaveUserAddress $action)
    {
        $this->validateRequest($request);

        // Convertimos Request a DTO
        $dto = AddressData::fromRequest($request);
        
        // Ejecutamos la lógica encapsulada
        $action->execute($request->user(), $dto);

        return redirect()->route('profile.index')->with('success', 'Operación exitosa.');
    }

    public function edit(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) abort(403);

        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon', 'latitude', 'longitude')
            ->get();

        return Inertia::render('Profile/Addresses/Edit', [
            'address' => $address,
            'activeBranches' => $branches
        ]);
    }

    public function update(Request $request, UserAddress $address, SaveUserAddress $action)
    {
        if ($address->user_id !== Auth::id()) abort(403);

        $this->validateRequest($request);

        $dto = AddressData::fromRequest($request);

        // Pasamos la dirección vieja ($address) para que la Action haga el reemplazo
        $action->execute($request->user(), $dto, $address);

        return redirect()->route('profile.index')->with('success', 'Operación exitosa.');
    }

    public function destroy(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) abort(403);
        
        $address->delete();
        
        // Opcional: Si borró la dirección activa, el middleware global debería detectar 
        // y resetear el contexto en la siguiente petición.

        return back()->with('success', 'Dirección eliminada.');
    }

    public function makeDefault(Request $request, UserAddress $address, ShopContextService $contextService)
    {
        if ($address->user_id !== Auth::id()) abort(403);

        // La lógica del modelo UserAddress (booted) se encarga de poner false a las demás
        $address->update(['is_default' => true]);

        // Si tiene cobertura, forzamos cambio de contexto inmediato
        if ($address->branch_id) {
            $request->user()->update(['branch_id' => $address->branch_id]);
            $contextService->setContext($address->branch_id, $address->id);
        }

        return back()->with('success', 'Ubicación principal actualizada.');
    }

    // Validación común extraída
    protected function validateRequest(Request $request): void
    {
        $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_default'=> 'boolean'
        ]);
    }
}