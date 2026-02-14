<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Driver/Auth/Login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        // Intentar autenticar en el silo de conductores
        if (Auth::guard('driver')->attempt(
            ['phone' => $request->phone, 'password' => $request->password],
            $request->remember
        )) {
            $request->session()->regenerate();

            return redirect()->intended(route('driver.dashboard'));
        }

        throw ValidationException::withMessages([
            'phone' => 'Las credenciales no coinciden con nuestros registros de conductores.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('driver')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('driver.login');
    }
}