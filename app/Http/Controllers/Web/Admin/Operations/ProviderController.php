<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operations\Provider;
use App\DTOs\Admin\Operations\Provider\ProviderData;
use App\Http\Requests\Admin\Operations\Provider\StoreProviderRequest;
use App\Http\Requests\Admin\Operations\Provider\UpdateProviderRequest;
use App\Actions\Admin\Operations\Provider\ListProviders;
use App\Actions\Admin\Operations\Provider\UpsertProvider;
use App\Actions\Admin\Operations\Provider\DeleteProvider;
use App\Http\Resources\Admin\Operations\Provider\ProviderResource;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class ProviderController extends Controller
{
    public function index(Request $request, ListProviders $action): Response
    {
        $search = (string) $request->search;
        $page = $request->page ?: 1;
        
        $version = Cache::get('admin_providers_version', 1);
        $cacheKey = "admin_providers_v{$version}_search_" . md5($search . $page);

        $cachedData = Cache::remember($cacheKey, 3600, function () use ($action, $search) {
            $paginator = $action->execute($search);
            return [
                'items' => ProviderResource::collection($paginator->items())->resolve(),
                'meta'  => [
                    'next_cursor' => $paginator->nextCursor()?->encode(),
                    'prev_cursor' => $paginator->previousCursor()?->encode(), // RECTIFICACIÓN: previousCursor()
                ]
            ];
        });

        return Inertia::render('Admin/Operations/Providers/Index', [
            'providers'  => $cachedData,
            'filters'    => $request->only(['search']),
            'can_manage' => true
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Operations/Providers/Workspace', [
            'provider' => null
        ]);
    }

    public function store(StoreProviderRequest $request, UpsertProvider $action): RedirectResponse
    {
        $action->execute(ProviderData::fromRequest($request));

        return redirect()->route('admin.operations.providers.index')
            ->with('success', 'Socio comercial incorporado al sistema de abastecimiento.');
    }

    public function edit(Provider $provider): Response
    {
        return Inertia::render('Admin/Providers/Workspace', [
            'provider' => (new ProviderResource($provider))->resolve()
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider, UpsertProvider $action): RedirectResponse
    {
        $action->execute(ProviderData::fromRequest($request, $provider->id));

        return redirect()->route('admin.operations.providers.index')
            ->with('success', 'Historial relacional del proveedor sincronizado.');
    }

    public function destroy(Provider $provider, DeleteProvider $action): RedirectResponse
    {
        try {
            $action->execute($provider);
            return redirect()->back()->with('success', 'Proveedor purgado del ecosistema operacional.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}