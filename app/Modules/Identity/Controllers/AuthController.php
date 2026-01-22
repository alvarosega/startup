<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
// use App\Models\Role; // YA NO NECESITAMOS IMPORTAR EL MODELO ROL MANUALMENTE
use App\Modules\Identity\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Branch;
use App\Models\UserAddress;
use App\Models\UserProfile;

class AuthController extends Controller
{
    public function showLogin() { return Inertia::render('Auth/Login'); }
    
    public function showRegister() { 
        $branches = Branch::where('is_active', true)
            ->select('id', 'name', 'coverage_polygon')
            ->get();

        return Inertia::render('Auth/Register', [
            'activeBranches' => $branches
        ]); 
    }

    public function register(RegisterRequest $request)
    {
        // 1. Capturar Session ID
        $guestSessionId = $request->session()->getId();

        return DB::transaction(function () use ($request, $guestSessionId) {
            
            // 2. Crear usuario (Auth)
            $user = User::create([
                'phone' => $request->phone, 
                'password' => Hash::make($request->password),
                'is_active' => true,
                'avatar_type' => $request->avatar_type ?? 'icon',
                'avatar_source' => $request->avatar_source ?? 'avatar_1.svg',
            ]);

            // 3. Dirección (Solo si es Cliente)
            if ($request->role === 'client' && $request->latitude) {
                UserAddress::create([
                    'user_id' => $user->id,
                    'alias' => $request->alias ?? 'Casa',
                    'address' => $request->address ?? 'Ubicación seleccionada en mapa',
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

            // 5. Asignación de Rol
            // Si el frontend envía 'client', lo convertimos a 'customer' (según tu seeder)
            // Si el frontend envía 'driver', lo mantenemos.
            $roleToAssign = ($request->role === 'driver') ? 'driver' : 'customer';
            $user->assignRole($roleToAssign);

            // 6. Crear Perfil (CON DATOS EXTENDIDOS)
            // Aquí es donde aplicamos la corrección
            $profileData = [
                'user_id' => $user->id,
                // Recibimos nombres si vienen en el request (Drivers)
                'first_name' => $request->first_name, 
                'last_name' => $request->last_name,
            ];

            if ($roleToAssign === 'driver') {
                $profileData['license_number'] = $request->license_number;
                $profileData['vehicle_type'] = $request->vehicle_type;
                $profileData['license_plate'] = $request->license_plate;
                // Por seguridad, un driver nuevo no está verificado hasta que Admin lo apruebe
                $profileData['is_identity_verified'] = false; 
            }

            UserProfile::create($profileData);

            // 7. Login y Carrito
            Auth::login($user);
            $this->mergeGuestCart($user, $guestSessionId);

            return redirect()->route('shop.index');
        });
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);
    
        $phone = str_replace(' ', '', $credentials['phone']); 
    
        if (!str_starts_with($phone, '+')) {
            $phone = '+591' . $phone;
        }
    
        $credentials['phone'] = $phone;
        
        $guestSessionId = $request->session()->getId();

        if (Auth::attempt($credentials, $request->remember)) {
            
            $request->session()->regenerate();
            $user = $request->user();
            $user->update(['last_login_at' => now()]);
    
            $this->mergeGuestCart($user, $guestSessionId);
    
            // Redirección por Roles (Usando métodos Spatie)
            if ($user->hasRole('logistics_operator')) {
                return redirect()->intended(route('admin.logistics.dashboard'));
            }
    
            // Verificamos cualquier rol administrativo
            if ($user->hasAnyRole(['super_admin', 'branch_admin', 'logistics_manager', 'finance_manager', 'inventory_manager'])) {
                 return redirect()->intended(route('admin.dashboard')); 
            }
            if ($user->hasRole('driver')) {
                return redirect()->intended(route('driver.dashboard'));
            }
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

    private function mergeGuestCart(User $user, string $guestSessionId)
    {
        $guestCart = Cart::where('session_id', $guestSessionId)->first();

        if ($guestCart) {
            $userCart = Cart::where('user_id', $user->id)
                ->where('branch_id', $guestCart->branch_id)
                ->first();

            if ($userCart) {
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
                $guestCart->update([
                    'user_id' => $user->id,
                    'session_id' => null
                ]);
            }
        }
    }
}