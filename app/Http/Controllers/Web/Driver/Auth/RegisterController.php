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
use App\Http\Requests\Driver\Auth\ValidateStep1Request;
use App\Models\Branch;

class RegisterController extends Controller
{
    use ValidatesGlobalIdentity;

    public function create()
    {
        // La vista de registro de conductor no necesita las sucursales.
        // Devolver un array vacío es lo correcto aquí.
        return Inertia::render('Driver/Auth/Register', [
            'activeBranches' => [],
        ]);
    }
    public function validateStep1(ValidateStep1Request $request)
    {
        // Si llega aquí, es porque los datos son únicos y válidos
        Log::info('[DriverRegister] Step 1 validado con éxito', ['email' => $request->email]);
        
        return response()->json(['status' => 'success']);
    }

    public function store(RegisterRequest $request, RegisterDriverAction $action)
    {
        Log::info('[DriverRegister] Intento de registro recibido', $request->all());
    
        try {
            $data = RegisterDriverData::fromRequest($request);
            $driver = $action->execute($data);
    
            Log::info('[DriverRegister] Driver creado con éxito', ['id' => $driver->id]);
    
            Auth::guard('driver')->login($driver);
            $request->session()->regenerate();
    
            return redirect()->route('driver.dashboard');
    
        } catch (\Exception $e) {
            Log::error('[DriverRegister] Error en el guardado', [
                'mensaje' => $e->getMessage(),
                'linea'   => $e->getLine()
            ]);
    
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
