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
        // El DTO ahora encapsula la lógica de extracción del Guest UUID
        $data = LoginCustomerData::fromRequest($request);

        try {
            // Ejecución Atómica: Autenticación + Sincronización de Carrito (Interno)
            $action->execute($data); 

            // Seguridad de Sesión
            $request->session()->regenerate();
            
            return redirect()->intended(route('customer.index'));
            
        } catch (ValidationException $e) {
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