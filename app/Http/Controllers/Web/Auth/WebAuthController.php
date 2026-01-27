<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
// Actions y DTOs
use App\Actions\Auth\LoginUser;
use App\DTOs\Auth\LoginData; // Asegúrate de que LoginData también acepte 'phone'
use App\Actions\Auth\LogoutUser;
use App\Http\Requests\Auth\RegisterRequest;
use App\Actions\Auth\RegisterUser;
use App\DTOs\Auth\RegisterData;
use App\Models\Branch; 
use App\Http\Requests\Auth\RegisterDriverRequest;
use App\Actions\Auth\RegisterDriver;
use App\DTOs\Auth\RegisterDriverData;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Shop\CartController;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * CORRECCIÓN: Login con Teléfono
     */
    public function login(Request $request, LoginUser $action)
    {
        // 1. Validar Teléfono en lugar de Email
        $credentials = $request->validate([
            'phone'    => ['required', 'string'], // Cambiado de 'email' a 'phone'
            'password' => ['required', 'string'],
        ]);

        // Sanitización rápida (opcional pero recomendada si el usuario mete espacios)
        // Si tu DB guarda +591, asegúrate de que el request venga igual o límpialo aquí
        // $credentials['phone'] = ... lógica de limpieza si es necesaria ...

        // 2. Capturar sesión de invitado ANTES de loguear
        $guestSessionId = $request->session()->getId();

        // 3. Intentar Autenticación
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            
            $user = Auth::user();

            // 4. Regenerar Sesión
            $request->session()->regenerate();

            // 5. Fusionar Carritos
            CartController::mergeGuestCart($guestSessionId, $user);

            // 6. Redirección
            return redirect()->intended(route('dashboard'));
        }

        // Si falla
        $request->session()->flash('error', 'Las credenciales no coinciden.');
        
        return back()->withErrors([
            'phone' => 'El número de celular o la contraseña son incorrectos.',
        ])->onlyInput('phone');
    }

    // --- EL RESTO DE TUS MÉTODOS SE MANTIENEN IGUAL ---

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

        return redirect()->route('dashboard');
    }

    public function validateStep1(\Illuminate\Http\Request $request)
    {
        $input = $request->all();

        if (isset($input['phone'])) {
            $cleanPhone = str_replace([' ', '-', '(', ')'], '', $input['phone']);
            if (!str_starts_with($cleanPhone, '+')) {
                $cleanPhone = '+591' . $cleanPhone;
            }
            $input['phone'] = $cleanPhone;
        }

        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'phone'    => ['required', 'string', 'unique:users,phone', 'regex:/^\+[0-9]{8,15}$/'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'phone.unique' => 'Este número de celular ya está registrado.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'phone.regex' => 'El formato del teléfono no es válido (ej: +59177712345).'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['message' => 'Paso 1 validado correctamente']);
    }

    public function registerDriver(RegisterDriverRequest $request, RegisterDriver $action)
    {
        $data = RegisterDriverData::fromRequest($request);
        $user = $action->execute($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function confirmPassword(Request $request)
    {
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