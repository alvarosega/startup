<?php

namespace App\Http\Controllers\Web\Driver\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Actions\Driver\Auth\RegisterDriverAction;
use App\DTOs\Driver\Auth\RegisterDriverData;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create() 
    {
        return Inertia::render('Driver/Auth/Register');
    }

    public function validateStep1(Request $request)
    {
        Log::channel('stack')->info('--- DRIVER REGISTER: VALIDANDO STEP 1 ---', ['data' => $request->only(['email', 'phone'])]);

        try {
            $request->validate([
                'phone' => ['required', 'string', 'max:20', 'unique:drivers,phone'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:drivers,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ], [
                'phone.unique' => 'Este número ya está registrado en la flota.',
                'email.unique' => 'Este correo ya está registrado en la flota.',
            ]);

            Log::info('Step 1 validado con éxito.');
            return response()->json(['status' => 'success']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Fallo de validación en Step 1', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function store(Request $request, RegisterDriverAction $action)
    {
        Log::channel('stack')->info('--- INICIO PROCESO STORE DRIVER ---');

        // 1. Validación de Entrada
        try {
            $validated = $request->validate([
                'phone' => 'required|unique:drivers,phone',
                'email' => 'required|email|unique:drivers,email',
                'password' => 'required|confirmed|min:8',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'license_number' => 'required|unique:driver_details,license_number',
                'license_plate' => 'required|string',
                'vehicle_type' => 'required|in:moto,car,truck',
            ]);
            Log::info('Validación del Request exitosa.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Fallo de validación final en Store', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            // 2. Instanciación del DTO
            Log::info('Intentando crear DTO RegisterDriverData...');
            $data = new RegisterDriverData(
                phone:         $request->phone,
                email:         $request->email,
                password:      $request->password,
                firstName:     $request->first_name,
                lastName:      $request->last_name,
                licenseNumber: $request->license_number,
                licensePlate:  $request->license_plate,
                vehicleType:   $request->vehicle_type,
                avatarType:    $request->avatar_type ?? 'icon',
                avatarSource:  $request->avatar_source ?? 'avatar_1.svg',
                avatarFile:    $request->file('avatar_file')
            );
            Log::info('DTO creado.');

            // 3. Ejecución de la Acción (Punto crítico)
            Log::info('Ejecutando RegisterDriverAction...');
            $driver = $action->execute($data);
            
            if (!$driver) {
                throw new \Exception('La acción RegisterDriverAction devolvió un resultado nulo.');
            }
            Log::info('Driver creado con éxito.', ['id' => $driver->id]);

            // 4. Intento de Login con Guard Driver
            Log::info('Intentando login con guard: driver');
            Auth::guard('driver')->login($driver);
            
            if (!Auth::guard('driver')->check()) {
                Log::error('El guard "driver" no pudo mantener la sesión iniciada.');
            }

            Log::info('Proceso completado. Redirigiendo...');
            return redirect()->route('driver.dashboard')
                ->with('message', '¡Registro completado! Bienvenido.');

        } catch (\Exception $e) {
            Log::error('FALLO CRÍTICO EN STORE DRIVER:', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                // 'trace' => $e->getTraceAsString() // Opcional si el error es muy profundo
            ]);

            return back()->withErrors([
                'error' => 'No se pudo completar el registro: ' . $e->getMessage()
            ])->withInput();
        }
    }
}