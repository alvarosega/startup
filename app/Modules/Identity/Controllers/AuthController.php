<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Modules\Identity\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin() { return Inertia::render('Auth/Login'); }
    public function showRegister() { return Inertia::render('Auth/Register'); }

    /**
     * Registro de nuevo usuario (Nivel 1)
     */
// En App\Modules\Identity\Controllers\AuthController.php

    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            
            // 1. Preparar datos del avatar
            $avatarType = $request->avatar_type;
            $avatarSource = $request->avatar_source; // Por defecto el icono

            // 2. Crear usuario BASE
            $user = User::create([
                'phone' => $request->phone, // Ya viene limpio del Request
                'password' => Hash::make($request->password),
                'is_active' => true,
                'avatar_type' => $avatarType,
                'avatar_source' => $avatarSource, // Guardamos temporalmente el icono o string vacio
            ]);

            // 3. Si subió imagen propia, la guardamos ahora usando el ID del usuario
            if ($avatarType === 'custom' && $request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store("avatars/{$user->id}", 'private');
                // Actualizamos el usuario con la ruta real
                $user->update(['avatar_source' => $path]);
            }

            // 4. Asignar Rol
            $roleName = ($request->role === 'client') ? 'client' : 'driver'; // Ojo: en tus seeders usa nombres en inglés
            $role = Role::where('name', $roleName)->first();
            if ($role) $user->roles()->attach($role->id);

            // 5. Crear Perfil Vacío (Necesario para el porcentaje de completitud)
            \App\Models\UserProfile::create(['user_id' => $user->id]);

            // 6. Auditoría Legal
            DB::table('legal_agreements_log')->insert([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accepted_at' => now(),
            ]);

            Auth::login($user);

            return redirect()->route('shop.index');
        });
    }

    /**
     * Inicio de sesión con redirección por rol
     */
    public function login(Request $request)
    {
        // 1. Validamos la entrada básica
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);
    
        // 2. NORMALIZACIÓN DE DATOS
        $phone = $credentials['phone'];
        $phone = str_replace(' ', '', $phone); // Limpiamos espacios
    
        // Si no empieza con '+', asumimos que es un número local de Bolivia (+591)
        if (!str_starts_with($phone, '+')) {
            $phone = '+591' . $phone;
        }
    
        // Actualizamos la credencial a usar
        $credentials['phone'] = $phone;
    
        // 3. Intento de Login
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $request->user()->update(['last_login_at' => now()]);
    
            // --- LÓGICA DE RUTEO ESCALABLE ---
            $user = $request->user();
    
            // CASO A: PERSONAL OPERATIVO (Dashboards Especializados)
            // El Operador Logístico va a su panel táctico (Alertas y stock)
            if ($user->hasRole('logistics_operator')) {
                return redirect()->intended(route('admin.logistics.dashboard'));
            }
            
            // (Futuro) Repartidores
            if ($user->hasRole('driver')) {
                // return redirect()->route('driver.dashboard');
            }
    
            // CASO B: GERENCIA Y ADMINISTRACIÓN (Dashboard General)
            // Usamos 'hasAnyRole' para verificar múltiples roles en una sola línea
            if ($user->hasAnyRole([
                'super_admin',
                'branch_admin',
                'identity_auditor',
                'logistics_manager', // El Gerente sí ve todo
                'finance_manager',
                'inventory_manager'
            ])) {
                 return redirect()->intended(route('admin.dashboard')); 
            }
    
            // CASO C: Clientes normales -> Home / Catálogo
            return redirect()->route('shop.index');
        }
    
        // 4. Feedback de error
        return back()->withErrors([
            'phone' => 'Credenciales incorrectas.',
        ]);
    }

    /**
     * Cierre de sesión y limpieza de tokens
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('shop.index');
    }
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar_type' => 'required|in:icon,custom',
            'avatar_source' => 'required_if:avatar_type,icon',
            'avatar_file' => 'required_if:avatar_type,custom|image|max:1024',
        ]);
    
        $user = auth()->user();
    
        if ($request->avatar_type === 'custom') {
            // Guardar en disco privado
            $path = $request->file('avatar_file')->store("avatars/{$user->id}", 'private');
            $user->update([
                'avatar_type' => 'custom',
                'avatar_source' => $path
            ]);
        } else {
            $user->update([
                'avatar_type' => 'icon',
                'avatar_source' => $request->avatar_source
            ]);
        }
    
        return redirect()->route('profile.wizard');
    }
}