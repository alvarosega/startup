<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Actions\Driver\Auth\RegisterDriverAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Traits\ValidatesGlobalIdentity;

class RegisterController extends Controller
{
    use ValidatesGlobalIdentity;
    public function create()
    {
        return Inertia::render('Driver/Auth/Register');
    }

    public function validateStep1(Request $request)
    {
        // Normalizamos el teléfono manualmente antes de validar (Igual que en el FormRequest)
        if ($request->has('phone') && !empty($request->phone)) {
            $cleanPhone = preg_replace('/[^\+0-9]/', '', $request->phone);
            if (!str_starts_with($cleanPhone, '+')) {
                $cleanPhone = '+' . $cleanPhone;
            }
            $request->merge(['phone' => $cleanPhone]);
        }

        // Usamos las reglas de oro del Trait
        $request->validate([
            'phone'    => $this->globalPhoneRules(), // <--- AHORA SÍ REVISA LA DB
            'email'    => $this->globalEmailRules(), // <--- AHORA SÍ REVISA LA DB
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        
        return response()->json(['success' => true]);
    }

    public function store(RegisterRequest $request, RegisterDriverAction $action)
    {
        try {
            $data = RegisterDriverData::fromRequest($request);
            $driver = $action->execute($data);

            Auth::guard('driver')->login($driver);

            return redirect()->route('driver.dashboard');
            
        } catch (\Exception $e) {
            // Protección contra fallos silenciosos de base de datos
            return back()->withErrors(['phone' => 'Error interno: ' . $e->getMessage()])->withInput();
        }
    }
}