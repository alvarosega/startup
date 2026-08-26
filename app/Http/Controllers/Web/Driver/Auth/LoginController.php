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

    public function store(LoginRequest $request, LoginDriverAction $action): \Illuminate\Http\RedirectResponse
    {
        $data = LoginDriverData::fromRequest($request);
        $driver = $action->execute($data);

        $request->session()->regenerate();

        $statusValue = is_object($driver->status) ? $driver->status->value : (string) $driver->status;

        // Si está aprobado, va a la operación de órdenes; si está pendiente/rechazado, a su perfil informativo
        if ($statusValue === 'approved') {
            return redirect()->intended(route('driver.dashboard'));
        }

        return redirect()->route('driver.profile.index');
    }
    public function destroy(Request $request)
    {
        Auth::guard('driver')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('driver.login');
    }
}