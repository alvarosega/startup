<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\ForgotPasswordRequest; // <--- NUEVO
use App\Actions\Customer\Auth\SendResetCodeAction;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return Inertia::render('Customer/Auth/ForgotPassword');
    }

    public function sendResetCode(ForgotPasswordRequest $request, SendResetCodeAction $action)
    {
        try {
            $email = $request->validated('email');
            $action->execute($email);
    
            // Almacenamos el email en sesión (flash) en lugar de la URL
            return redirect()->route('password.reset')
                ->with('reset_email', $email)
                ->with('status', 'Código enviado con éxito.');
                
        } catch (\Exception $e) {
            Log::error('[ResetCode] ' . $e->getMessage());
            return back()->withErrors(['email' => 'Proceso no disponible temporalmente.']);
        }
    }
}