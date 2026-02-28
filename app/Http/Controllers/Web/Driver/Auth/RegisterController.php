<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Actions\Driver\Auth\RegisterDriverAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Driver/Auth/Register');
    }

    // Validador temporal para el Paso 1 (Frontend multipaso)
    public function validateStep1(Request $request)
    {
        // Se inyecta la lógica de validación parcial si la usas, o delegar al Request principal
        $request->validate([
            'phone'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        
        return response()->json(['success' => true]);
    }

    public function store(RegisterRequest $request, RegisterDriverAction $action)
    {
        $data = RegisterDriverData::fromRequest($request);
        
        $driver = $action->execute($data);

        // Login automático tras registro exitoso
        Auth::guard('driver')->login($driver);

        return redirect()->route('driver.dashboard');
    }
}