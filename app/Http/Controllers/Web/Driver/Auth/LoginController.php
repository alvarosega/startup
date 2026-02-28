<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\LoginRequest;
use App\Actions\Driver\Auth\LoginDriverAction;
use App\DTOs\Driver\Auth\LoginDriverData;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show() 
    { 
        return Inertia::render('Driver/Auth/Login'); 
    }

    public function store(LoginRequest $request, LoginDriverAction $action)
    {
        // 1. Transformación a DTO (Validación ya ocurrió en LoginRequest)
        $data = LoginDriverData::fromRequest($request);

        // 2. Ejecución Atómica. 
        // Si las credenciales fallan, la Action lanza ValidationException y Laravel redirige solo.
        // La Action también se encarga de revocar sesiones previas (Single Session Policy).
        $action->execute($data); 
            
        // 3. Orquestación HTTP
        $request->session()->regenerate();
        
        return redirect()->intended(route('driver.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('driver')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('driver.login');
    }
}