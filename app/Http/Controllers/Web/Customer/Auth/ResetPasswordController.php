<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Muestra la vista física para ingresar el código y la nueva clave
     */
    public function showResetForm($email)
    {
        return Inertia::render('Customer/Auth/ResetPassword', [
            'email' => $email
        ]);
    }
    public function reset(Request $request, \App\Actions\Customer\Auth\ResetPasswordAction $action)
    {
        // 1. Validación
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'code' => 'required|string|size:6',
            'password' => 'required|confirmed|min:8',
        ]);
    
        try {
            // 2. Orquestación (DTO implícito por brevedad en este paso)
            $action->execute($request->email, $request->code, $request->password);
    
            return redirect()->route('login')
                ->with('message', 'Contraseña actualizada correctamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[PasswordUpdate] ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error interno al actualizar la clave.']);
        }
    }
}