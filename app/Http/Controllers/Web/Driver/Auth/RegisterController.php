<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\Auth\RegisterRequest;
use App\DTOs\Driver\Auth\RegisterDriverData;
use App\Http\Requests\Driver\Auth\ValidateStep1Request;
use App\Actions\Driver\Auth\RegisterDriverAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // <--- IMPORTANTE
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Driver/Auth/Register');
    }

    public function validateStep1(ValidateStep1Request $request)
    {
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
            Log::error('[DriverRegister] Fallo en la transacción: ' . $e->getMessage());
            return back()->withErrors(['phone' => 'Hubo un problema al crear tu cuenta. Inténtalo de nuevo.'])->withInput();
        }
    }
}