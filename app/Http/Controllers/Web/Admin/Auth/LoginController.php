<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Actions\Admin\Auth\LoginAdminAction;
use App\DTOs\Admin\Auth\LoginAdminData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginAdminRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginController extends Controller
{
    public function showLogin(): InertiaResponse
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function login(LoginAdminRequest $request, LoginAdminAction $action): SymfonyResponse
    {
        $request->checkRateLimit(); 
    
        $data = LoginAdminData::fromRequest($request);
    
        try {
            $action->execute($data);
            
            $request->clearRateLimiter();
            $request->session()->regenerate();
            
            return redirect()->route('dashboard.index');
    
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

        return redirect()->route('login');
    }
}