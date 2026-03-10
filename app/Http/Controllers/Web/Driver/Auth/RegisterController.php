<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Http\Requests\Driver\Auth\ValidateStep1Request;
use App\Actions\Driver\Auth\RegisterDriverAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Traits\ValidatesGlobalIdentity;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Driver/Auth/Register');
    }

    public function validateStep1(ValidateStep1Request $request)
    {
        // El FormRequest ya validó y normalizó todo usando el Trait
        // Si llega aquí, significa que no hay colisiones en los 3 silos
        return back(); 
    }
    public function store(RegisterRequest $request, RegisterDriverAction $action)
    {
        try {
            $data = RegisterDriverData::fromRequest($request);
            $driver = $action->execute($data);

            Auth::guard('driver')->login($driver);

            return redirect()->route('driver.dashboard');
            
        } catch (\Exception $e) {
            Log::error('[DriverRegister] Error: ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Error interno al registrar el conductor.'])->withInput();
        }
    }
}