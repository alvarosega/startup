<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura
use App\DTOs\Provider\ProviderData;
use App\Http\Requests\Provider\StoreProviderRequest;
use App\Http\Requests\Provider\UpdateProviderRequest;
use App\Actions\Provider\CreateProvider;
use App\Actions\Provider\UpdateProvider;
use App\Http\Resources\ProviderResource;

class ProviderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Provider::class);

        $providers = Provider::query()
            ->when($request->search, function ($query, $search) {
                $query->where('company_name', 'like', "%{$search}%")
                      ->orWhere('commercial_name', 'like', "%{$search}%")
                      ->orWhere('tax_id', 'like', "%{$search}%");
            })
            ->orderBy('company_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Providers/Index', [
            'providers' => ProviderResource::collection($providers), // Usamos el Resource
            'filters' => $request->only(['search']),
            'can_manage' => auth()->user()->can('create', Provider::class)
        ]);
    }

    public function create()
    {
        $this->authorize('create', Provider::class);
        return Inertia::render('Admin/Providers/Create');
    }

    public function store(StoreProviderRequest $request, CreateProvider $action)
    {
        $this->authorize('create', Provider::class);
        
        $data = ProviderData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.providers.index')->with('success', 'Proveedor creado.');
    }

    public function edit(Provider $provider)
    {
        $this->authorize('update', $provider);
        
        // FORMA MÁS SEGURA: toArray(request()) devuelve el array puro sin wrappers de 'data'
        return Inertia::render('Admin/Providers/Edit', [
            'provider' => (new ProviderResource($provider))->toArray(request())
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider, UpdateProvider $action)
    {
        $this->authorize('update', $provider);

        $data = ProviderData::fromRequest($request);
        $action->execute($provider, $data);

        return redirect()->route('admin.providers.index')->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Provider $provider)
    {
        $this->authorize('delete', $provider);
        $provider->delete();
        
        return redirect()->route('admin.providers.index')->with('success', 'Proveedor eliminado.');
    }
}