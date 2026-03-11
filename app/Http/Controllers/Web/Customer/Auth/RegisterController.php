<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Branch;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Requests\Customer\Auth\ValidateStep1Request;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Actions\Customer\Auth\RegisterCustomerAction;
use App\Traits\ValidatesGlobalIdentity;

class RegisterController extends Controller
{
    use ValidatesGlobalIdentity;

    public function create()
    {
        $activeBranches = Branch::where('is_active', true)
            ->get(['id', 'name', 'latitude', 'longitude', 'coverage_polygon'])
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'latitude' => (float) $b->latitude,
                'longitude' => (float) $b->longitude,
                'coverage_polygon' => $b->coverage_polygon,
            ]);

        return Inertia::render('Customer/Auth/Register', ['activeBranches' => $activeBranches]);
    }

    public function validateStep1(ValidateStep1Request $request)
    {
        return back(); 
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action)
    {
        try {
            $data = RegisterCustomerData::fromRequest($request);
            
            // La acción es atómica. Si falla, lanza excepción y no loguea.
            $customer = $action->execute($data); 

            // Login explícito en el guard correcto
            Auth::guard('customer')->login($customer);
            
            // Destruimos la sesión anterior (vital para seguridad y evitar fantasmas)
            $request->session()->regenerate();
            
            // Redirección consistente con el LoginController
            return redirect()->intended(route('customer.shop.index'));

        } catch (\Exception $e) {
            Log::emergency('[CustomerRegister] Error en Action:', ['msg' => $e->getMessage()]);
            return back()->withErrors(['email' => 'Error crítico al crear la cuenta. Por favor intente de nuevo.']);
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