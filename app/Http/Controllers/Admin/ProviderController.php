<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Http\Requests\Provider\StoreProviderRequest;
use App\Http\Requests\Provider\UpdateProviderRequest;
use Inertia\Inertia;

class ProviderController extends Controller
{
    public function index()
    {
        // Ordenamos por nombre comercial (si existe) o razón social
        // COALESCE elige el primero que no sea nulo
        $providers = Provider::orderByRaw('COALESCE(commercial_name, company_name) ASC')
            ->get();

        return Inertia::render('Admin/Providers/Index', [
            'providers' => $providers
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Providers/Create');
    }

    public function store(StoreProviderRequest $request)
    {
        Provider::create($request->validated());
        return redirect()->route('admin.providers.index')->with('message', 'Proveedor registrado correctamente.');
    }

    public function edit(Provider $provider)
    {
        return Inertia::render('Admin/Providers/Edit', ['provider' => $provider]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        $provider->update($request->validated());
        return redirect()->route('admin.providers.index')->with('message', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Provider $provider)
    {
        // Validación lógica futura: 
        // if ($provider->purchases()->exists()) { return error... }
        
        $provider->delete();
        return redirect()->route('admin.providers.index')->with('message', 'Proveedor eliminado.');
    }
}