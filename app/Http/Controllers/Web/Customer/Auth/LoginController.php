<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Customer/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validar
        $credentials = $request->validate([
            'phone' => ['required', 'string'], // Acepta '+591...'
            'password' => ['required'],
        ]);

        // 2. Buscar usuario manualmente para diagnóstico
        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return back()->withErrors(['phone' => 'No existe cuenta con este número.']);
        }

        // 3. Verificar Contraseña
        if (!Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['phone' => 'Contraseña incorrecta.']);
        }

        // 4. Login
        if (Auth::guard('customer')->attempt(['phone' => $request->phone, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('shop.index'));
        }

        return back()->withErrors(['phone' => 'Error de autenticación.']);
    }

    public function destroy(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}