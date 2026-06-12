<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Customer\Auth\LoginRequest; 
use App\Actions\Customer\Auth\LoginCustomerAction;
use App\DTOs\Customer\Auth\LoginCustomerData;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Customer/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, LoginCustomerAction $action): RedirectResponse
    {
        // 1. Fase Preventiva: Verificación de Fuerza Bruta antes de procesar el DTO
        $request->checkRateLimit(); 

        $data = LoginCustomerData::fromRequest($request);

        try {
            // 2. Ejecución Atómica
            $action->execute($data); 

            // 3. Fase de Éxito: Limpieza de contadores de bloqueo
            $request->clearRateLimiter();

            // 4. Regeneración del identificador de sesión por seguridad de cookies
            $request->session()->regenerate();
            
            return redirect()->intended(route('customer.index'));
            
        } catch (ValidationException $e) {
            // 4. Fase de Fallo: Incremento en el limitador por intento fallido o cuenta inactiva
            $request->hitRateLimiter();
            
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('customer.index');
    }
}