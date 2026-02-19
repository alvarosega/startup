<?php
namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// Arquitectura de Capas
use App\DTOs\Admin\Provider\ProviderData;
use App\Http\Requests\Admin\Provider\StoreProviderRequest;
use App\Http\Requests\Admin\Provider\UpdateProviderRequest;
use App\Actions\Admin\Provider\ListProviders;
use App\Actions\Admin\Provider\CreateProvider;
use App\Actions\Admin\Provider\UpdateProvider;
use App\Actions\Admin\Provider\DeleteProvider;
use App\Http\Resources\Admin\Provider\ProviderResource;

class ProviderController extends Controller
{
    use AuthorizesRequests;
    
    public function index(Request $request, ListProviders $action): Response
    {
        $this->authorize('viewAny', Provider::class);

        $providers = $action->execute($request->search);

        return Inertia::render('Admin/Providers/Index', [
            'providers' => ProviderResource::collection($providers),
            'filters'   => $request->only(['search']),
            'can_manage' => $request->user()->can('create', Provider::class)
        ]);
    }

    /**
     * Orquestación: Renderiza formulario de creación.
     */
    public function create(): Response
    {
        $this->authorize('create', Provider::class);

        return Inertia::render('Admin/Providers/Create');
    }

    /**
     * Orquestación: Transforma Request en DTO y delega persistencia atómica.
     */
    public function store(StoreProviderRequest $request, CreateProvider $action): RedirectResponse
    {
        $this->authorize('create', Provider::class);

        $data = ProviderData::fromRequest($request);
        $action->execute($data);

        return redirect()->route('admin.providers.index')
            ->with('message', 'Proveedor registrado exitosamente.');
    }

    /**
     * Orquestación: Renderiza edición con sanitización via Resource.
     */
    public function edit(Provider $provider): Response
    {
        $this->authorize('update', $provider);

        return Inertia::render('Admin/Providers/Edit', [
            'provider' => (new ProviderResource($provider))->resolve()
        ]);
    }

    /**
     * Orquestación: Actualización vía DTO y Action.
     */
    public function update(UpdateProviderRequest $request, Provider $provider, UpdateProvider $action): RedirectResponse
    {
        $this->authorize('update', $provider);

        $data = ProviderData::fromRequest($request);
        $action->execute($provider, $data);

        return redirect()->route('admin.providers.index')
            ->with('message', 'Proveedor actualizado correctamente.');
    }

    /**
     * Orquestación: Eliminación atómica (Soft Delete).
     */
    public function destroy(Provider $provider, DeleteProvider $action): RedirectResponse
    {
        $this->authorize('delete', $provider);

        $action->execute($provider);

        return redirect()->route('admin.providers.index')
            ->with('message', 'Proveedor removido del sistema.');
    }
}