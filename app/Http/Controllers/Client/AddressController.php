<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Services\ShopContextService; // <--- 1. IMPORTANTE

class AddressController extends Controller
{
    protected $shopContext;

    // 2. INYECCIÓN DE DEPENDENCIA
    public function __construct(ShopContextService $shopContext)
    {
        $this->shopContext = $shopContext;
    }

    public function index()
    {
        return Inertia::render('Profile/Addresses/Index', [
            'addresses' => Auth::user()->addresses()->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function create()
    {
        // --- AÑADIR ESTE BLOQUE AL INICIO ---
        if (request()->user()->addresses()->count() >= 3) {
            return redirect()->route('addresses.index')
                ->with('error', 'Has alcanzado el límite de 3 direcciones.');
        }
        // ------------------------------------

        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon', 'latitude', 'longitude')
            ->get();

        return Inertia::render('Profile/Addresses/Create', [
            'activeBranches' => $branches
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->addresses()->count() >= 3) {
            return back()->withErrors(['limit' => 'Límite de direcciones alcanzado.']);
        }
        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_default'=> 'boolean' 
        ]);

        // Cálculo de Cobertura
        $coveringBranch = Branch::findCoveringBranch($validated['latitude'], $validated['longitude']);
        
        $validated['branch_id'] = $coveringBranch?->id;
        $validated['reference'] = $validated['details']; 
        unset($validated['details']);

        $newAddress = $request->user()->addresses()->create($validated);

        // 3. ACTUALIZACIÓN INMEDIATA DE CONTEXTO
        if ($newAddress->branch_id) {
            // A. Actualizar Perfil Usuario (Persistencia)
            $request->user()->update(['branch_id' => $newAddress->branch_id]);

            // B. Actualizar Sesión (Navegación Actual)
            // Esto arregla el bug: le decimos al sistema "¡Oye, cámbiate a esta sucursal YA!"
            $this->shopContext->setContext($newAddress->branch_id, $newAddress->id);
        }

        return redirect()->route('addresses.index')->with('success', 'Dirección guardada correctamente.');
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

    public function update(Request $request, UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) abort(403);

        // --- ELIMINAR ESTE BLOQUE QUE TE ESTÁ BLOQUEANDO ---
        /*
        if ($request->user()->addresses()->count() >= 3) {
            return back()->withErrors(['limit' => 'Límite alcanzado: Máximo 3 direcciones activas permitidas.']);
        }
        */
        // ---------------------------------------------------

        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_default'=> 'boolean'
        ]);

        $coveringBranch = Branch::findCoveringBranch($validated['latitude'], $validated['longitude']);
        $validated['branch_id'] = $coveringBranch?->id;
        $validated['reference'] = $validated['details'];
        unset($validated['details']);

        // Crear nueva (Inmutable)
        $newAddress = $request->user()->addresses()->create($validated);
        
        // Al borrar la anterior aquí, mantienes el conteo en 3
        $address->delete(); 

        // 4. ACTUALIZACIÓN INMEDIATA DE CONTEXTO
        if ($newAddress->branch_id) {
            $request->user()->update(['branch_id' => $newAddress->branch_id]);
            $this->shopContext->setContext($newAddress->branch_id, $newAddress->id);
        }

        return redirect()->route('addresses.index')->with('success', 'Dirección actualizada.');
    }
    
    public function makeDefault(Request $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) abort(403);

        // El modelo UserAddress ya tiene un evento 'saving' que pone false a las demás
        $address->update(['is_default' => true]);

        // Actualizamos contexto de la tienda y perfil
        if ($address->branch_id) {
            $request->user()->update(['branch_id' => $address->branch_id]);
            $this->shopContext->setContext($address->branch_id, $address->id);
        }

        return back()->with('success', 'Ubicación principal actualizada.');
    }
    // Para solucionar el Error 500
    public function destroy(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) abort(403);

        $address->delete();

        return back()->with('success', 'Dirección eliminada.');
    }

    // Para marcar como principal

}