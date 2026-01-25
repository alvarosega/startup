<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterData;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAddress;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisterUser
{
    public function execute(RegisterData $data): User
    {
        return DB::transaction(function () use ($data) {
            
            // 1. Crear Usuario
            $user = User::create([
                'phone' => $data->phone,
                'email' => $data->email, // <--- Guardamos Email
                'password' => Hash::make($data->password),
                'is_active' => true,
                'avatar_type' => $data->avatarType,
                'avatar_source' => $data->avatarSource ?? 'avatar_1.svg',
                'email_verified_at' => null, // <--- Explícitamente NO verificado
            ]);

            // 2. Manejo de Avatar Custom (Si subió archivo)
            if ($data->avatarType === 'custom' && $data->avatarFile) {
                // Guardamos en disco 'private' o 'public' según tu config
                $path = $data->avatarFile->store("avatars/{$user->id}", 'private');
                $user->update(['avatar_source' => $path]);
            }

            // 3. Asignar Rol
            $roleToAssign = ($data->role === 'driver') ? 'driver' : 'customer';
            $user->assignRole($roleToAssign);

            // 4. Crear Perfil
            UserProfile::create([
                'user_id' => $user->id,
                // Lógica de negocio: El registro inicial NO verifica identidad automáticamente
                // El usuario debe verificar su email después.
                'is_identity_verified' => false, 
            ]);

            // 5. Guardar Dirección (Solo si es Cliente y envió coordenadas)
            if ($roleToAssign === 'customer' && $data->latitude && $data->longitude) {
                UserAddress::create([
                    'user_id' => $user->id,
                    'alias' => $data->alias ?? 'Casa',
                    'address' => $data->address ?? 'Ubicación seleccionada',
                    'reference' => $data->details, // Ojo: en tu modelo se llama 'reference', en el form 'details'
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                    'branch_id' => $data->branchId,
                    'is_default' => true
                ]);
                
                // Si la dirección tiene sucursal, la asignamos al usuario como preferida
                if ($data->branchId) {
                    $user->update(['branch_id' => $data->branchId]);
                }
            }

            // 6. Fusión de Carrito (Lógica migrada)
            if ($data->guestSessionId) {
                $this->mergeGuestCart($user, $data->guestSessionId);
            }
            event(new Registered($user));

            return $user;
        });
    }

    /**
     * Mueve los items del carrito de invitado al carrito del usuario registrado.
     */
    private function mergeGuestCart(User $user, string $guestSessionId): void
    {
        $guestCart = Cart::where('session_id', $guestSessionId)->first();

        if ($guestCart) {
            // Buscamos si el usuario ya tenía un carrito en esa misma sucursal
            $userCart = Cart::where('user_id', $user->id)
                ->where('branch_id', $guestCart->branch_id)
                ->first();

            if ($userCart) {
                // Si ya tenía, movemos los items uno por uno
                foreach ($guestCart->items as $item) { // Asumiendo relación 'items' en modelo Cart
                    $existingItem = CartItem::where('cart_id', $userCart->id)
                        ->where('sku_id', $item->sku_id)
                        ->first();

                    if ($existingItem) {
                        $existingItem->increment('quantity', $item->quantity);
                    } else {
                        $item->update(['cart_id' => $userCart->id]);
                    }
                }
                // Borramos el carrito viejo de invitado porque ya quedó vacío/migrado
                $guestCart->forceDelete(); 
            } else {
                // Si el usuario no tenía carrito, simplemente nos adueñamos del de invitado
                $guestCart->update([
                    'user_id' => $user->id,
                    'session_id' => null // Ya no es de sesión, es de usuario
                ]);
            }
        }
    }
}