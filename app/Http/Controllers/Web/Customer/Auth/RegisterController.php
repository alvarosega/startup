<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

// Imports de nuestra arquitectura
use App\Models\Branch;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\DTOs\Customer\Auth\RegisterCustomerData;
use App\Actions\Customer\Auth\RegisterCustomerAction;

class RegisterController extends Controller
{
    public function create()
    {
        // Mantenemos la conversión Hex para el mapa frontend
        $activeBranches = Branch::where('is_active', true)
            ->select('id', 'name', 'latitude', 'longitude')
            ->get()
            ->map(function ($b) {
                $rawId = $b->getRawOriginal('id') ?? $b->id;
                $safeId = (is_string($rawId) && strlen($rawId) === 16 && !ctype_print($rawId)) 
                    ? bin2hex($rawId) 
                    : $b->id;
                
                return [
                    'id' => $safeId,
                    'name' => $b->name,
                    'latitude' => (float) $b->latitude,
                    'longitude' => (float) $b->longitude,
                ];
            });

        return Inertia::render('Customer/Auth/Register', [
            'activeBranches' => $activeBranches
        ]);
    }

    public function validateStep1(Request $request)
    {
        // Validación manual para el paso 1 (no usamos el Request completo aquí para no validar todo)
        $request->validate([
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        return response()->json(['status' => 'success']);
    }

    public function store(RegisterRequest $request, RegisterCustomerAction $action)
    {
        try {
            // 1. Transformar Request a DTO
            $data = RegisterCustomerData::fromRequest($request);

            // 2. Ejecutar Acción
            $customer = $action->execute($data);

            // 3. Login Automático
            Auth::guard('customer')->login($customer);
            
            // 4. Regenerar sesión por seguridad
            $request->session()->regenerate();

            Log::info('Login automático exitoso. Redirigiendo.');

            // 5. Redirección Limpia (Evitar enviar variables que contengan binarios)
            return redirect()->intended('/');

        } catch (\Exception $e) {
            Log::error('ERROR REGISTRO: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error del sistema: ' . $e->getMessage()]);
        }
    }
}