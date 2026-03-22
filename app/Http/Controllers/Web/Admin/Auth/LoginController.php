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
use App\Http\Resources\Admin\Auth\AdminResource;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse; // <--- NECESARIO

class LoginController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function login(LoginAdminRequest $request, LoginAdminAction $action): SymfonyResponse
    {
        // 1. Fase de Validación y Throttle
        $request->checkRateLimit(); 
    
        // 2. Fase de Contrato
        $data = LoginAdminData::fromRequest($request);
    
        try {
            // 3. Fase de Ejecución Atómica
            $action->execute($data);
            
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            $request->hitRateLimiter();
            throw $e;
        }
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super_admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}