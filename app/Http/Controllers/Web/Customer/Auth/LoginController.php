<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
// IMPORTACIÓN QUIRÚRGICA:
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
        // 1. Transformación a DTO (Datos inmutables)
        $data = LoginCustomerData::fromRequest($request);
    
        try {
            // 2. Ejecución de la lógica de negocio
            $action->execute($data);
            
            // 3. Gestión de sesión
            $request->session()->regenerate();
            if ($request->has('guest_client_uuid')) {
                $syncAction->execute(new SyncCartDTO(
                    customerId: Auth::guard('customer')->id(),
                    guestUuid: $request->input('guest_client_uuid'),
                    branchId: app(\App\Services\ShopContextService::class)->getActiveBranchId()
                ));
            }
            return redirect()->intended(route('shop.index'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[LoginFailure] ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Error interno en el inicio de sesión.']);
        }
    }

    public function destroy(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}