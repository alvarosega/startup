<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Customer\Auth\LoginRequest; 
use App\Actions\Customer\Auth\LoginCustomerAction;
use App\DTOs\Customer\Auth\LoginCustomerData;
use App\Actions\Customer\Cart\SyncGuestCartAction; // INYECCIÓN

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Customer/Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, LoginCustomerAction $action, SyncGuestCartAction $syncCartAction): RedirectResponse
    {
        $request->checkRateLimit(); 

        // Captura preventiva del UUID de invitado antes de la mutación de la sesión
        $guestUuid = $request->session()->get('guest_client_uuid');

        $data = LoginCustomerData::fromRequest($request);

        try {
            $action->execute($data); 

            $request->clearRateLimiter();

            // Regeneración de seguridad obligatoria
            $request->session()->regenerate();
            
            // Invocación explícita de unificación pos-login
            $syncCartAction->execute((string) Auth::guard('customer')->id(), $guestUuid);

            return redirect()->intended(route('customer.index'));
            
        } catch (ValidationException $e) {
            $request->hitRateLimiter();
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('customer.index');
    }
}