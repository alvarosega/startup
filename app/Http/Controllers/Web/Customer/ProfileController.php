<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        /** @var \App\Models\Customer $user */
        $user = Auth::guard('customer')->user();
        
        // Cargar relaciones vitales
        $user->load('profile', 'addresses');

        // 1. PREPARAR DIRECCIONES (Binario -> Hex)
        // 1. DIRECCIONES
        $addresses = $user->addresses()
        ->orderBy('is_default', 'desc')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($addr) {
            // OPCIÓN A: Usar toArray() (Recomendada si actualizaste el Modelo CustomerAddress)
            // return $addr->toArray(); 

            // OPCIÓN B: Manual (Infalible)
            // Convertimos explícitamente los binarios a Hex para que JSON no falle
            return [
                'id' => bin2hex($addr->getRawOriginal('id') ?? $addr->id),
                'alias' => $addr->alias,
                'address' => $addr->address,
                'reference' => $addr->reference,
                'latitude' => (float) $addr->latitude,   // Asegurar que sea número
                'longitude' => (float) $addr->longitude, // Asegurar que sea número
                'branch_id' => $addr->branch_id ? bin2hex($addr->getRawOriginal('branch_id')) : null,
                'is_default' => (bool) $addr->is_default,
            ];
        });

        // 2. PREPARAR DATA DEL USUARIO (Estructura anidada para Vue)
        $userData = [
            'id' => bin2hex($user->getRawOriginal('id') ?? $user->id), // ID Hex
            'email' => $user->email,
            'phone' => $user->phone,
            // AQUÍ ESTÁ EL CAMBIO CLAVE: Anidamos 'profile'
            'profile' => [
                'first_name' => $user->profile?->first_name ?? '',
                'last_name'  => $user->profile?->last_name ?? '',
                'birth_date' => $user->profile?->birth_date,
                'gender'     => $user->profile?->gender,
                'avatar_type'=> $user->profile?->avatar_type ?? 'icon',
                'avatar_source' => $user->profile?->avatar_source ?? 'avatar_1.svg',
                'is_identity_verified' => false // O el campo que tengas
            ]
        ];

        // 3. SUCURSALES (Conversión segura)
        $activeBranches = Branch::where('is_active', true)
            ->select('id', 'name', 'latitude', 'longitude')
            ->get()
            ->map(function ($branch) {
                $rawId = $branch->getRawOriginal('id') ?? $branch->id;
                // Detección de binario
                $idHex = (strlen($rawId) === 16 && !ctype_print($rawId)) 
                            ? bin2hex($rawId) 
                            : $branch->id;

                return [
                    'id' => $idHex, 
                    'name' => $branch->name,
                    'latitude' => (float) $branch->latitude,
                    'longitude' => (float) $branch->longitude,
                ];
            });

        // 4. RETORNO A VUE
        return Inertia::render('Customer/Profile/Index', [
            'user' => $userData,        // Vue espera 'user'
            'addresses' => $addresses,  // Vue espera 'addresses'
            'activeBranches' => $activeBranches
        ]);
    }
    public function update(Request $request)
    {
        $user = Auth::guard('customer')->user();

        // Validación condicional del email
        $emailRules = ['required', 'email'];
        if ($request->email !== $user->email) {
            // Validamos contra la tabla customers
            $emailRules[] = 'unique:customers,email';
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => $emailRules, 
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|string|in:M,F,O',
        ]);

        try {
            if ($request->email !== $user->email) {
                $user->update(['email' => $validated['email']]);
            }

            // Usamos updateOrCreate con el ID binario del usuario
            $user->profile()->updateOrCreate(
                ['customer_id' => $user->id], // El ID binario que tiene el modelo en memoria
                [
                    'first_name' => $validated['first_name'],
                    'last_name'  => $validated['last_name'],
                    'birth_date' => $validated['birth_date'],
                    'gender'     => $validated['gender'],
                ]
            );

            return back()->with('success', 'Perfil actualizado correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function updateAvatar(Request $request) 
    {
        $request->validate([
            'avatar_type' => 'required|in:icon,custom',
            'avatar_file' => 'nullable|image|max:3072',
            'avatar_source' => 'nullable|string'
        ]);
         
        $user = Auth::guard('customer')->user();
        $source = 'avatar_1.svg';

        if ($request->avatar_type === 'custom' && $request->hasFile('avatar_file')) {
            if ($user->profile?->avatar_type === 'custom' && $user->profile->avatar_source) {
                Storage::disk('public')->delete($user->profile->avatar_source);
            }
            
            // Usamos bin2hex para asegurar un nombre de carpeta válido en Windows/Linux
            $rawId = $user->getRawOriginal('id') ?? $user->id;
            $folderId = (strlen($rawId) === 16 && !ctype_print($rawId)) ? bin2hex($rawId) : $user->id;
            
            $folder = "customers/{$folderId}/avatars";
            $source = $request->file('avatar_file')->store($folder, 'public');
        } else {
            $source = $request->avatar_source ?? 'avatar_1.svg';
        }
        
        $user->profile()->updateOrCreate(
            ['customer_id' => $user->id],
            [
                'avatar_type' => $request->avatar_type,
                'avatar_source' => $source
            ]
        );

        return back()->with('success', 'Avatar actualizado.');
    }
}