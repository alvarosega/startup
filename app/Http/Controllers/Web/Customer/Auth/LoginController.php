<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Customer\Auth\LoginRequest; 
use App\Actions\Customer\Auth\LoginCustomerAction;
use App\Actions\Customer\Cart\CartMergeAction;
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

    public function store(
        LoginRequest $request, 
        LoginCustomerAction $action,
        CartMergeAction $mergeAction // Experto en fusión
    ): RedirectResponse {
        $data = LoginCustomerData::fromRequest($request);

        try {
            // 1. Ejecución de autenticación
            $action->execute($data); 
            $request->session()->regenerate();
            
            // 2. PROTOCOLO DE FUSIÓN (Merge)
            // Recuperamos el rastro del invitado antes de que se pierda la sesión antigua
            $guestUuid = $request->header('X-Guest-UUID') ?? session('guest_client_uuid');
            $customerId = (string) Auth::guard('customer')->id();

            if ($guestUuid) {
                $mergeAction->execute($guestUuid, $customerId);
            }

            // 3. Redirección limpia (El Middleware inyectará el Resource del usuario)
            return redirect()->route('customer.shop.index');
            
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