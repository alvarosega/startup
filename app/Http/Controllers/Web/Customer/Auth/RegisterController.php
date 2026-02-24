<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Branch;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\Traits\ValidatesGlobalIdentity;
use App\Http\Requests\Customer\Auth\ValidateStep1Request;

class RegisterController extends Controller
{
    use ValidatesGlobalIdentity;

    // MODIFICAR el método create()
    public function create()
    {
        $activeBranches = Branch::where('is_active', true)
            ->get(['id', 'name', 'latitude', 'longitude', 'coverage_polygon']) // <--- AÑADIR coverage_polygon
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'latitude' => (float) $b->latitude,
                'longitude' => (float) $b->longitude,
                'coverage_polygon' => $b->coverage_polygon, // <--- PASAR EL ARRAY/JSON
            ]);

        return Inertia::render('Customer/Auth/Register', ['activeBranches' => $activeBranches]);
    }
    // Cambiar el typehint del método
    public function validateStep1(ValidateStep1Request $request)
    {
        // Si llega aquí, la validación del FormRequest ya pasó
        return response()->json(['status' => 'success']);
    }

    // app/Http/Controllers/Web/Customer/Auth/RegisterController.php

    public function store(\App\Http\Requests\Customer\Auth\RegisterRequest $request, RegisterCustomerAction $action)
    {
        // Ahora que inyectamos RegisterRequest, Laravel llama automáticamente 
        // a prepareForValidation y normalizeIdentityData()
        
        Log::info('[CustomerRegister] Datos validados detectados:', $request->validated());

        try {
            // Usamos el método validated() del FormRequest inyectado
            $data = RegisterCustomerData::fromRequest($request);
            
            $customer = $action->execute($data);

            Auth::guard('customer')->login($customer);
            $request->session()->regenerate();
            if ($request->has('guest_client_uuid')) {
                $syncAction->execute(new SyncCartDTO(
                    customerId: Auth::guard('customer')->id(),
                    guestUuid: $request->input('guest_client_uuid'),
                    branchId: app(\App\Services\ShopContextService::class)->getActiveBranchId()
                ));
            }
            return redirect()->intended('/');

        } catch (\Exception $e) {
            Log::emergency('[CustomerRegister] Error en Action:', ['msg' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error en el registro: ' . $e->getMessage()]);
        }
    }
        // app/Http/Controllers/Web/Customer/Auth/RegisterController.php
    public function show()
    {
        return Inertia::render('Customer/Auth/Login', [
            'activeBranches' => Branch::where('is_active', true)->get(), // <--- DEBE TENER DATOS
        ]);
    }
}