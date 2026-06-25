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
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProviderController extends Controller
{
    /**
     * Renderiza en tiempo real de forma indexada directo desde el motor SQL.
     */
    public function index(Request $request, ListProviders $action): Response
    {
        $search = $request->filled('search') ? (string) $request->search : null;

        return Inertia::render('Admin/Operations/Providers/Index', [
            'providers'  => $action->execute($search),
            'filters'    => $request->only(['search']),
            'can_manage' => true
        ]);
    }

    /**
     * Redirecciona formalmente a la vista limpia de creación.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Operations/Providers/Create', [
            'provider' => null
        ]);
    }

    /**
     * Persiste e incorpora el nuevo socio comercial.
     */
    public function store(StoreProviderRequest $request, UpsertProvider $action): RedirectResponse
    {
        $action->execute(ProviderData::fromRequest($request));

        return redirect()->route('admin.operations.providers.index')
            ->with('success', 'Socio comercial incorporado al sistema de abastecimiento.');
    }

    /**
     * Sincroniza y fragmenta la vista de edición aislando el Workspace ambiguo.
     */
    public function edit(Provider $provider): Response
    {
        $mappedProvider = [
            'id' => (string) $provider->id,
            'company_name' => (string) $provider->company_name,
            'commercial_name' => $provider->commercial_name ? (string) $provider->commercial_name : null,
            'slug' => (string) $provider->slug,
            'tax_id' => (string) $provider->tax_id,
            'internal_code' => $provider->internal_code ? (string) $provider->internal_code : null,
            'contact_name' => $provider->contact_name ? (string) $provider->contact_name : null,
            'email_orders' => $provider->email_orders ? (string) $provider->email_orders : null,
            'phone' => $provider->phone ? (string) $provider->phone : null,
            'address' => $provider->address ? (string) $provider->address : null,
            'city' => $provider->city ? (string) $provider->city : null,
            'lead_time_days' => (int) $provider->lead_time_days,
            'min_order_value' => (float) $provider->min_order_value,
            'credit_days' => (int) $provider->credit_days,
            'credit_limit' => (float) $provider->credit_limit,
            'is_active' => (bool) $provider->is_active,
            'notes' => $provider->notes ? (string) $provider->notes : null,
        ];

        return Inertia::render('Admin/Operations/Providers/Edit', [
            'provider' => $mappedProvider
        ]);
    }

    /**
     * Actualiza el historial relacional basándose en el ID de la ruta.
     */
    public function update(UpdateProviderRequest $request, Provider $provider, UpsertProvider $action): RedirectResponse
    {
        $action->execute(ProviderData::fromRequest($request, $provider->id));

        return redirect()->route('admin.operations.providers.index')
            ->with('success', 'Historial relacional del proveedor sincronizado.');
    }

    /**
     * Borrado lógico coercitivo.
     */
    public function destroy(Provider $provider, DeleteProvider $action): RedirectResponse
    {
        try {
            $action->execute($provider);
            return redirect()->route('admin.operations.providers.index')
                ->with('success', 'Proveedor purgado del ecosistema operacional.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}