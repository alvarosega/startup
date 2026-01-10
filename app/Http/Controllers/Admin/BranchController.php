<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BranchController extends Controller
{

    public function index()
    {
        return Inertia::render('Admin/Branches/Index', [
            'branches' => Branch::orderBy('id', 'desc')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Branches/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'city' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'is_active' => 'boolean',
            
            // 1. Ubicación Física (El Pin)
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            // 2. Área de Cobertura (El Polígono)
            // Debe ser un array y tener al menos 3 puntos para formar una figura
            'coverage_polygon' => 'required|array|min:3', 
            'coverage_polygon.*' => 'array|size:2', // Cada punto es [lat, lng]
        ]);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')->with('message', 'Sucursal creada correctamente.');
    }
    public function edit(Branch $branch)
    {
        return Inertia::render('Admin/Branches/Edit', [
            'branch' => $branch
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            // unique:tabla,columna,ID_A_IGNORAR
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'city' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'is_active' => 'boolean',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'coverage_polygon' => 'required|array|min:3', 
            'coverage_polygon.*' => 'array|size:2',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')->with('message', 'Sucursal actualizada correctamente.');
    }
}