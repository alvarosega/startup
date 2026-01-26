<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
        // Enviamos polígonos para que el frontend dibuje o valide visualmente
        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon', 'latitude', 'longitude') // Agregué lat/lng para los marcadores
            ->get();

        return Inertia::render('Profile/Addresses/Create', [
            'activeBranches' => $branches
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            // is_default puede venir del form si añades un checkbox
            'is_default'=> 'boolean' 
        ]);

        // 1. BLINDAJE DE COBERTURA (Backend Authority)
        // Calculamos la sucursal real basada en las coordenadas
        $coveringBranch = Branch::findCoveringBranch($validated['latitude'], $validated['longitude']);
        
        // Inyectamos el ID calculado (puede ser null si está fuera de zona)
        $validated['branch_id'] = $coveringBranch?->id;
        $validated['reference'] = $validated['details']; // Mapeo details -> reference
        unset($validated['details']);

        $newAddress = $request->user()->addresses()->create($validated);

        // Si esta dirección tiene cobertura, actualizamos el contexto del usuario
        if ($newAddress->branch_id) {
            $request->user()->update(['branch_id' => $newAddress->branch_id]);
        }

        return redirect()->route('addresses.index')->with('success', 'Dirección guardada correctamente.');
    }

    public function edit(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

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
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_default'=> 'boolean'
        ]);

        // 1. BLINDAJE DE COBERTURA
        $coveringBranch = Branch::findCoveringBranch($validated['latitude'], $validated['longitude']);
        $validated['branch_id'] = $coveringBranch?->id;
        $validated['reference'] = $validated['details'];
        unset($validated['details']);

        // 2. ESTRATEGIA INMUTABLE
        // Creamos la nueva
        $newAddress = $request->user()->addresses()->create($validated);

        // Borramos la vieja (Soft Delete)
        $address->delete();

        // 3. ACTUALIZAR CONTEXTO
        // Si el usuario editó su dirección por defecto o la nueva tiene cobertura, actualizamos su perfil
        if ($newAddress->branch_id && ($newAddress->is_default || $address->is_default)) {
            $request->user()->update(['branch_id' => $newAddress->branch_id]);
        }

        return redirect()->route('addresses.index')->with('success', 'Dirección actualizada correctamente.');
    }
}