<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProviderController extends Controller
{
    use AuthorizesRequests;

    // ELIMINAMOS EL CONSTRUCTOR QUE CAUSABA EL ERROR 500
    // public function __construct() { ... }

    public function index(Request $request)
    {
        // 1. Seguridad Explícita: ¿Puede ver la lista?
        $this->authorize('viewAny', Provider::class);

        $providers = Provider::orderByRaw('COALESCE(commercial_name, company_name) ASC')
            ->when($request->search, function ($query, $search) {
                $query->where('company_name', 'like', "%{$search}%")
                      ->orWhere('commercial_name', 'like', "%{$search}%")
                      ->orWhere('tax_id', 'like', "%{$search}%");
            })
            ->paginate(10) // Paginamos para no saturar la vista
            ->withQueryString();

        return Inertia::render('Admin/Providers/Index', [
            'providers' => $providers,
            'filters' => $request->only(['search']),
            // Pasamos el permiso para que el botón "Crear" se muestre u oculte
            'can_manage' => auth()->user()->can('create', Provider::class)
        ]);
    }

    public function create()
    {
        // 2. Seguridad Explícita: ¿Puede crear?
        $this->authorize('create', Provider::class);

        return Inertia::render('Admin/Providers/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Provider::class);

        // Validación manual aquí o mediante StoreProviderRequest si lo creaste
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|max:20|unique:providers,tax_id',
            'email_orders' => 'nullable|email',
            'phone' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'lead_time_days' => 'integer|min:0',
            'min_order_value' => 'numeric|min:0',
            'is_active' => 'boolean'
        ]);

        Provider::create($validated);

        return redirect()->route('admin.providers.index')->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit(Provider $provider)
    {
        // 3. Seguridad Explícita: ¿Puede editar? (Usamos 'update' en la policy)
        $this->authorize('update', $provider);

        return Inertia::render('Admin/Providers/Edit', [
            'provider' => $provider
        ]);
    }

    public function update(Request $request, Provider $provider)
    {
        $this->authorize('update', $provider);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|max:20|unique:providers,tax_id,' . $provider->id,
            'email_orders' => 'nullable|email',
            'phone' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'lead_time_days' => 'integer|min:0',
            'min_order_value' => 'numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $provider->update($validated);

        return redirect()->route('admin.providers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Provider $provider)
    {
        // 4. Seguridad Explícita: ¿Puede eliminar?
        $this->authorize('delete', $provider);

        // Validación de Integridad (Opcional pero recomendada)
        // if ($provider->purchases()->exists()) { ... }
        
        $provider->delete();
        return redirect()->route('admin.providers.index')->with('success', 'Proveedor eliminado.');
    }
}