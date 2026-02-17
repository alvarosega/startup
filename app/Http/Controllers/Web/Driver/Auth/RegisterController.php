<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Actions\Driver\Auth\RegisterDriverAction;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
        Log::info('[DriverRegister] Validando Step 1', ['email' => $request->email]);

        // Validación Global: Evita que un Customer se registre como Driver con el mismo correo
        $request->validate([
            'phone'    => $this->globalPhoneRules(),
            'email'    => $this->globalEmailRules(),
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        return response()->json(['status' => 'success']);
    }

    public function store(RegisterRequest $request, RegisterDriverAction $action)
    {
        // NOTA: No se requiere $request->validate() aquí. RegisterRequest ya lo hizo.
        
        try {
            Log::info('[DriverRegister] Iniciando persistencia');

            // Estandarización: Usar el factory del DTO
            $data = RegisterDriverData::fromRequest($request);
            
            $driver = $action->execute($data);
            
            Auth::guard('driver')->login($driver);
            $request->session()->regenerate();

            Log::info('[DriverRegister] Éxito', ['id' => bin2hex($driver->getRawOriginal('id'))]);

            return redirect()->route('driver.dashboard')
                ->with('message', '¡Registro completado! Bienvenido.');

        } catch (\Exception $e) {
            Log::error('[DriverRegister] Fallo crítico', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ]);

            return back()->withErrors([
                'error' => 'No se pudo completar el registro: ' . $e->getMessage()
            ])->withInput();
        }
    }
}