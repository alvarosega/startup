<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Actions\Admin\Auth\LoginAdminAction;
use App\DTOs\Admin\Auth\LoginAdminData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginAdminRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse; // <--- NECESARIO

class LoginController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Procesa el login.
     * NOTA: Retornamos SymfonyResponse para aceptar Inertia::location
     */
    public function login(LoginAdminRequest $request, LoginAdminAction $action): SymfonyResponse
    {
        $request->authenticate();
        $data = LoginAdminData::fromRequest($request);

        try {
            $action->execute($data);
            $request->session()->regenerate();
            $request->session()->save(); 
            $request->hitRateLimiter();

            // CORRECCIÓN: Usar route() genera la URL correcta dinámicamente
            // independientemente de lo que pongas en el .env
            return Inertia::location(route('admin.dashboard')); 

        } catch (\Exception $e) {
            $request->hitRateLimiter();
            throw $e;
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}