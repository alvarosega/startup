<?php

namespace App\Http\Controllers\Web\Admin\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\DTOs\Admin\Provider\ProviderData;
use App\Actions\Admin\Provider\{ListProviders, UpsertProvider, DeleteProvider};
use App\Http\Requests\Admin\Provider\{StoreProviderRequest, UpdateProviderRequest};
use App\Http\Resources\Admin\Provider\ProviderResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProviderController extends Controller
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            // Esto vincula el controlador a la Policy automáticamente
            new Middleware('can:viewAny,App\Models\Provider', only: ['index']),
            new Middleware('can:create,App\Models\Provider', only: ['create', 'store']),
            new Middleware('can:view,provider', only: ['show']),
            new Middleware('can:update,provider', only: ['edit', 'update']),
            new Middleware('can:delete,provider', only: ['destroy']),
        ];
    }
    /**
     * REGLA 3.A: Rendimiento Extremo con Caché de lectura
     */
    public function index(Request $request, ListProviders $action): Response
    {
        $search = (string) $request->search;
        $page = $request->page ?: 1;
        
        // 1. Leemos la versión actual del módulo (Si no existe, es la 1)
        $version = Cache::get('admin_providers_version', 1);
        
        // 2. Construimos la llave atada a la versión
        $cacheKey = "admin_providers_v{$version}_search_" . md5($search . $page);

        $providers = Cache::remember($cacheKey, 3600, fn() => 
            ProviderResource::collection($action->execute($search))
        );

        return Inertia::render('Admin/Providers/Index', [
            'providers' => $providers,
            'filters'   => $request->only(['search']),
            'can_manage' => $request->user()->can('create', Provider::class)
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Providers/Create');
    }

    /**
     * REGLA 2.B: Unificación vía Upsert (Atomicidad)
     */
    public function store(StoreProviderRequest $request, UpsertProvider $action): RedirectResponse
    {
        $action->execute(ProviderData::fromRequest($request));

        return redirect()->route('admin.providers.index')
            ->with('message', 'PROTOCOLO_ALTA: Socio comercial registrado exitosamente.');
    }

    public function edit(Provider $provider): Response
    {
        return Inertia::render('Admin/Providers/Edit', [
            'provider' => new ProviderResource($provider)
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider, UpsertProvider $action): RedirectResponse
    {
        $action->execute(ProviderData::fromRequest($request, $provider->id));

        return redirect()->route('admin.providers.index')
            ->with('message', 'PROTOCOLO_SINC: Datos del socio comercial actualizados.');
    }

    public function destroy(Provider $provider, DeleteProvider $action): RedirectResponse
    {
        try {
            $action->execute($provider);
            return redirect()->back()
                ->with('message', 'PROTOCOLO_PURGA: Nodo eliminado del sistema.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}