<?php

namespace App\Http\Controllers\Web\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Customer\CustomerResetCodeMail;
use Inertia\Inertia;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{

    public function showLinkRequestForm()
    {
        return Inertia::render('Customer/Auth/ForgotPassword');
    }
    public function sendResetCode(Request $request, \App\Actions\Customer\Auth\SendResetCodeAction $action)
    {
        // 1. Validación (Firewall)
        $request->validate(['email' => 'required|email|exists:customers,email']);
    
        try {
            // 2. Orquestación
            $action->execute($request->email);
    
            return redirect()->route('password.reset', ['email' => $request->email])
                ->with('status', 'Código enviado con éxito.');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[ResetCode] ' . $e->getMessage());
            return back()->withErrors(['email' => 'No se pudo enviar el código.']);
        }
    }
}