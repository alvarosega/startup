<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Actions\Auth\LoginUser;
use App\DTOs\Auth\LoginData;
use App\Actions\Auth\LogoutUser;
use App\Http\Requests\Auth\RegisterRequest;
use App\Actions\Auth\RegisterUser;
use App\DTOs\Auth\RegisterData;
use App\Models\Branch; 
use App\Http\Requests\Auth\RegisterDriverRequest;
use App\Actions\Auth\RegisterDriver;
use App\DTOs\Auth\RegisterDriverData;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request, LoginUser $action)
    {
        $request->ensureIsNotRateLimited();
        
        try {
            $data = LoginData::fromRequest($request);
            $result = $action->execute($data); 
            
            Auth::login($result['user'], $request->boolean('remember'));
            $request->session()->regenerate();

            // CORRECCIÓN: Enviamos todo a la ruta dashboard inteligente
            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            $request->hitRateLimiter();
            return back()->withErrors([
                'email' => $e->getMessage(),
            ]);
        }
    }

    // NOTA: He eliminado la función 'redirectBasedOnRole' porque ahora la lógica está en routes/web.php

    public function logout(Request $request, LogoutUser $action)
    {
        $user = Auth::user();
        if ($user) {
            $action->execute($user);
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showRegister()
    {
        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon')
            ->get();

        return Inertia::render('Auth/Register', [
            'activeBranches' => $branches
        ]);
    }

    public function register(RegisterRequest $request, RegisterUser $action)
    {
        $data = RegisterData::fromRequest($request);
        $user = $action->execute($data);

        Auth::login($user);
        $request->session()->regenerate();

        // CORRECCIÓN: Antes iba fijo a shop.index, ahora va a dashboard
        return redirect()->route('dashboard');
    }

/**
     * Valida el Paso 1 del Registro (Datos de Cuenta) de forma asíncrona.
     */
    public function validateStep1(\Illuminate\Http\Request $request)
    {
        $input = $request->all();

        // 1. Sanitización de Teléfono
        // Limpiamos el formato visual que pueda venir del frontend (ej: "777-12345")
        if (isset($input['phone'])) {
            $cleanPhone = str_replace([' ', '-', '(', ')'], '', $input['phone']);
            
            // Si el número no empieza con '+', asumimos que es local (Bolivia) y agregamos +591
            if (!str_starts_with($cleanPhone, '+')) {
                $cleanPhone = '+591' . $cleanPhone;
            }
            
            $input['phone'] = $cleanPhone;
        }

        // 2. Validación
        // Usamos la fachada Validator manualmente para poder inyectar el input sanitizado
        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'phone'    => ['required', 'string', 'unique:users,phone', 'regex:/^\+[0-9]{8,15}$/'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // <--- CRÍTICO: Agregado
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'phone.unique' => 'Este número de celular ya está registrado.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'phone.regex' => 'El formato del teléfono no es válido (ej: +59177712345).'
        ]);

        // 3. Respuesta de Error (422)
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 4. Respuesta de Éxito
        return response()->json(['message' => 'Paso 1 validado correctamente']);
    }

    public function registerDriver(RegisterDriverRequest $request, RegisterDriver $action)
    {
        $data = RegisterDriverData::fromRequest($request);
        $user = $action->execute($data);

        Auth::login($user);
        $request->session()->regenerate();

        // CORRECCIÓN: El driver va a dashboard -> web.php lo detecta -> driver.dashboard
        return redirect()->route('dashboard');
    }

    public function confirmPassword(Request $request)
    {
        // ... (Tu lógica se mantiene igual) ...
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => ['La contraseña ingresada es incorrecta.']
            ]);
        }

        $request->session()->passwordConfirmed();

        return back();
    }
}