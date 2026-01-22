<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\UserAddress; // Importamos el modelo correcto
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Muestra el listado de direcciones del usuario.
     */
    public function index()
    {
        return Inertia::render('Profile/Addresses/Index', [
            'addresses' => Auth::user()->addresses()->orderBy('created_at', 'desc')->get()
        ]);
    }

    /**
     * Muestra el formulario de creación con el mapa y los polígonos.
     */
    public function create()
    {
        // Enviamos las sucursales activas con sus polígonos para que el mapa JS calcule la cobertura
        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon')
            ->get();

        return Inertia::render('Profile/Addresses/Create', [
            'activeBranches' => $branches
        ]);
    }

    /**
     * Guarda la nueva dirección.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            // VALIDACIÓN CRÍTICA:
            // Aceptamos el ID de la sucursal si el frontend detectó cobertura.
            // Si es null, se guarda igual (Estrategia de Cosecha de Datos).
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $request->user()->addresses()->create($validated);

        return redirect()->route('addresses.index')->with('success', 'Dirección guardada correctamente.');
    }
    
    /**
     * Muestra el formulario de edición.
     */
    public function edit(UserAddress $address)
    {
        // Seguridad: Verificar que la dirección pertenezca al usuario autenticado
        if ($address->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta dirección.');
        }

        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon')
            ->get();

        return Inertia::render('Profile/Addresses/Edit', [
            'address' => $address,
            'activeBranches' => $branches
        ]);
    }

    /**
     * Actualiza la dirección (Creando una nueva y archivando la vieja).
     */
    public function update(Request $request, UserAddress $address)
    {
        // Seguridad
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'alias'     => 'required|string|max:50',
            'address'   => 'required|string|max:255',
            'details'   => 'nullable|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            // También validamos branch_id aquí por si la cobertura cambió al mover el pin
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        // ESTRATEGIA DE HISTORIAL INMUTABLE:
        // 1. Creamos una NUEVA dirección con los datos actualizados
        $request->user()->addresses()->create($validated);

        // 2. Eliminamos (Soft Delete) la dirección VIEJA
        // Esto mantiene la integridad histórica de pedidos pasados y nos sirve para mapas de calor
        $address->delete();

        return redirect()->route('addresses.index')->with('success', 'Dirección actualizada correctamente.');
    }
}