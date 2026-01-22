<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileWizardController extends Controller
{
    // Vista del Wizard
    public function show() 
    {
        return Inertia::render('Auth/ProfileWizard', [
            'step' => Auth::user()->getProfileCompletionPercentage() < 40 ? 1 : 2
        ]);
    }

    // Paso 1: Identidad
    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date|before:-18 years', // Regla A.10/B.2
        ]);

        Auth::user()->profile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        return back()->with('success', 'Paso 1 completado.');
    }

    // Paso 2: Email y Recuperación (B.6)
    public function storeStep2(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->update(['email' => $data['email']]);
        
        // Disparar verificación de email
        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Enlace de verificación enviado.');
    }
}
