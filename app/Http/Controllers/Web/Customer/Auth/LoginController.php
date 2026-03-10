<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
// IMPORTACIÓN QUIRÚRGICA:
use App\Http\Requests\Customer\Auth\LoginRequest; 
use App\Actions\Customer\Auth\LoginCustomerAction;
use App\DTOs\Customer\Auth\LoginCustomerData;

class LoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Customer/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, LoginCustomerAction $action)
    {
        $data = LoginCustomerData::fromRequest($request);
    
        try {
            $action->execute($data); // <--- La acción ya sincroniza el carrito por dentro
            
            $request->session()->regenerate();
            return redirect()->intended(route('customer.shop.index')); // Usa el nombre de ruta correcto
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[LoginFailure] ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Error interno en el inicio de sesión.']);
        }
    }

    public function destroy(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}