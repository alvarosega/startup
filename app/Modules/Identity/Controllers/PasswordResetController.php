<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Envía el enlace de recuperación solo si el email está verificado (Regla B.6)
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['phone' => 'required|numeric']);

        $user = User::where('phone', $request->phone)->first();

        // Si el usuario no existe o no ha verificado su correo, bloqueamos el proceso
        if (!$user || !$user->email_verified_at) {
            return back()->withErrors([
                'phone' => 'No es posible recuperar la cuenta por este medio. Contacte a soporte técnico.'
            ]);
        }

        // Enviamos el correo de recuperación utilizando el sistema nativo de Laravel
        // Se envía al email asociado al registro del celular
        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['phone' => __($status)]);
    }

    public function showResetForm(Request $request, $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email
        ]);
    }
}