<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
        // El DTO captura el guest_client_uuid enviado dinámicamente desde Vue
        $data = LoginCustomerData::fromRequest($request);
    
        try {
            // El Action realiza: attempt -> sync carritos (por sucursal)
            $action->execute($data); 
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('customer.shop.index'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[LoginFailure] ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Credenciales incorrectas o error de sistema.']);
        }
    }

    public function destroy(Request $request)
    {
        \Illuminate\Support\Facades\Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.shop.index');
    }
}