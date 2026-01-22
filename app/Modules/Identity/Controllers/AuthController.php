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
use App\Models\Cart;      // Importante
use App\Models\CartItem;  // Importante
use App\Models\Branch;    // Importante
use App\Models\UserAddress; // Importante

class AuthController extends Controller
{
    public function showLogin() { return Inertia::render('Auth/Login'); }
    
    public function showRegister() { 
        // Enviamos las sucursales para que el mapa del registro funcione
        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon')
            ->get();

        return Inertia::render('Auth/Register', [
            'activeBranches' => $branches
        ]); 
    }

    /**
     * Registro de nuevo usuario (Completo con Dirección y Carrito)
     */
    public function register(RegisterRequest $request)
    {
        // 1. CRÍTICO: Capturamos el ID de la sesión del invitado ANTES de cualquier login
        // Si lo hacemos después de Auth::login, Laravel ya habrá regenerado el ID y perderemos el carrito.
        $guestSessionId = $request->session()->getId();

        return DB::transaction(function () use ($request, $guestSessionId) {
            
            // 2. Crear usuario
            $user = User::create([
                'phone' => $request->phone, 
                'password' => Hash::make($request->password),
                'is_active' => true,
                'avatar_type' => $request->avatar_type ?? 'icon',
                'avatar_source' => $request->avatar_source ?? 'avatar_1.svg',
            ]);

            // 3. Dirección (Si aplica)
            if ($request->role === 'client' && $request->latitude) {
                UserAddress::create([
                    'user_id' => $user->id,
                    'alias' => $request->alias ?? 'Casa',
                    'address' => $request->address,
                    'details' => $request->details,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'branch_id' => $request->branch_id,
                    'is_default' => true
                ]);
            }

            // 4. Avatar Custom
            if ($request->avatar_type === 'custom' && $request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store("avatars/{$user->id}", 'private');
                $user->update(['avatar_source' => $path]);
            }

            // 5. Roles y Perfil
            $roleName = ($request->role === 'client') ? 'client' : 'driver'; 
            $role = Role::where('name', $roleName)->first();
            if ($role) $user->roles()->attach($role->id);

            \App\Models\UserProfile::create(['user_id' => $user->id]);

            // 6. Login (Esto regenera la sesión, por eso capturamos el ID antes)
            Auth::login($user);
            
            // 7. Fusión de Carrito USANDO EL ID VIEJO
            $this->mergeGuestCart($user, $guestSessionId);

            return redirect()->route('shop.index');
        });
    }

    /**
     * Inicio de sesión corregido
     */
    public function login(Request $request)
    {
        // 1. Validar
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);
    
        // 2. Normalizar teléfono
        $phone = str_replace(' ', '', $credentials['phone']); 
    
        if (!str_starts_with($phone, '+')) {
            $phone = '+591' . $phone;
        }
    
        $credentials['phone'] = $phone;
        
        // 3. Capturar ID de sesión ANTES de loguear
        $guestSessionId = $request->session()->getId();

        // 4. Intento de Login
        if (Auth::attempt($credentials, $request->remember)) {
            
            $request->session()->regenerate();
            $user = $request->user();
            $user->update(['last_login_at' => now()]);
    
            // 5. Fusión de Carrito
            $this->mergeGuestCart($user, $guestSessionId);
    
            // 6. Redirección por Roles
            
            // Operativos
            if ($user->hasRole('logistics_operator')) {
                return redirect()->intended(route('admin.logistics.dashboard'));
            }
    
            // Administrativos
            if ($user->hasAnyRole(['super_admin', 'branch_admin', 'logistics_manager', 'finance_manager', 'inventory_manager'])) {
                 return redirect()->intended(route('admin.dashboard')); 
            }
    
            // Clientes (Checkout o Home)
            return redirect()->intended(route('shop.index'));
        }
    
        return back()->withErrors([
            'phone' => 'Credenciales incorrectas.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('shop.index');
    }

    public function validateStep1(Request $request)
    {
        $input = $request->all();
        if (isset($input['phone'])) {
            $phone = str_replace([' ', '-', '(', ')'], '', $input['phone']);
            if (!str_starts_with($phone, '+')) {
                $phone = '+591' . $phone;
            }
            $input['phone'] = $phone;
        }

        $validator = \Illuminate\Support\Facades\Validator::make($input, [
            'phone' => ['required', 'string', 'regex:/^\+[0-9]{8,15}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json(['message' => 'Step 1 OK']);
    }

    /**
     * Mueve el carrito de la sesión de invitado al usuario autenticado.
     */
    private function mergeGuestCart(User $user, string $guestSessionId)
    {
        $guestCart = Cart::where('session_id', $guestSessionId)->first();

        if ($guestCart) {
            // Ver si el usuario ya tenía carrito en esa sucursal
            $userCart = Cart::where('user_id', $user->id)
                ->where('branch_id', $guestCart->branch_id)
                ->first();

            if ($userCart) {
                // Fusión de items
                foreach ($guestCart->items as $item) {
                    $existingItem = CartItem::where('cart_id', $userCart->id)
                        ->where('sku_id', $item->sku_id)
                        ->first();

                    if ($existingItem) {
                        $existingItem->increment('quantity', $item->quantity);
                        $item->delete();
                    } else {
                        $item->update(['cart_id' => $userCart->id]);
                    }
                }
                $guestCart->delete();
            } else {
                // Asignación directa
                $guestCart->update([
                    'user_id' => $user->id,
                    'session_id' => null
                ]);
            }
        }
    }
}