<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Customer\Auth\LoginRequest; 
use App\Actions\Customer\Auth\LoginCustomerAction;
use App\DTOs\Customer\Auth\LoginCustomerData;
use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Support\Facades\Auth;

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
            $action->execute($data); 
            $request->session()->regenerate();
            
            // Forzamos el Resource para que el estado inicial de Inertia sea limpio
            $customer = new CustomerResource(Auth::guard('customer')->user());
    
            return redirect()->route('customer.shop.index')->with('user', $customer);
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
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