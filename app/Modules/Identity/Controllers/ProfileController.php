<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Muestra el "Centro de Mando" (Dashboard del Perfil).
     * Solo lectura y accesos directos.
     */
    public function index()
    {
        // Cargamos los datos necesarios para las tarjetas del dashboard
        // Asumiendo que User tiene relación 'profile' y 'addresses'
        $user = Auth::user()->load(['profile', 'addresses']);

        return Inertia::render('Profile/Index', [
            'user' => $user,
            // Enviamos contadores o datos extra si lo requieres
            'addresses_count' => $user->addresses->count(), 
        ]);
    }

    /**
     * Muestra el formulario de edición (Datos Personales).
     */
    public function edit()
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user()->load('profile'),
            'status' => session('status'),
        ]);
    }

    /**
     * Procesa la actualización de los datos del perfil.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        \Log::info('--- INICIO UPDATE PERFIL ---');
    
        try {
            return DB::transaction(function () use ($request, $user) {
                // 1. Actualizar Email (Solo si cambió)
                if ($request->filled('email') && $request->email !== $user->email) {
                    $user->update(['email' => $request->email]);
                    // Opcional: Invalidar verificación de email si cambia
                    $user->email_verified_at = null;
                    $user->save();
                    \Log::info('Email actualizado en users');
                }
    
                // 2. Limpiar datos vacíos a NULL
                $profileData = $request->safe()->except(['email']);
                
                foreach ($profileData as $key => $value) {
                    if ($value === '') $profileData[$key] = null;
                }
    
                // 3. Guardar en UserProfile
                $profile = $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
    
                \Log::info('PERFIL GUARDADO EXITOSAMENTE ID: ' . $profile->id);
                
                // Redirigimos al Centro de Mando con mensaje de éxito
                return to_route('profile.index')->with('message', 'Datos actualizados correctamente.');
            });
        } catch (\Throwable $e) {
            \Log::error('ERROR CRÍTICO AL GUARDAR: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error del sistema: ' . $e->getMessage()]);
        }
    }
/**
     * Actualiza la foto de perfil (Versión Robusta)
     */
    public function updateAvatar(Request $request)
    {
        // 1. Limpieza inicial: Si es 'icon', forzamos avatar_file a null
        if ($request->avatar_type === 'icon') {
            $request->merge(['avatar_file' => null]);
        }

        // 2. Validación Condicional (Separada para evitar conflictos)
        if ($request->avatar_type === 'custom') {
            $request->validate([
                'avatar_file' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB
            ]);
        } else {
            $request->validate([
                'avatar_type' => 'required|in:icon',
                'avatar_source' => 'required|string',
            ]);
        }

        $user = Auth::user();

        try {
            // CASO A: Subida de Foto Propia
            if ($request->avatar_type === 'custom' && $request->hasFile('avatar_file')) {
                
                $file = $request->file('avatar_file');
                
                // Generar nombre único
                $filename = 'avatar_' . time() . '.webp';
                $path = "avatars/{$user->id}/{$filename}";

                // Procesar con Intervention Image (Reducir a 300x300)
                // Si falla Intervention, usamos guardado normal como fallback
                if (class_exists(\Intervention\Image\Laravel\Facades\Image::class)) {
                    $image = \Intervention\Image\Laravel\Facades\Image::read($file);
                    $image->cover(300, 300);
                    $encoded = $image->toWebp(80);
                    // Guardar en disco 'private'
                    \Illuminate\Support\Facades\Storage::disk('private')->put($path, (string) $encoded);
                } else {
                    // Fallback: Si no está instalada la librería, guardar directo
                    $path = $file->storeAs("avatars/{$user->id}", $filename, 'private');
                }

                // Borrar foto vieja si existía
                if ($user->avatar_source && $user->avatar_type === 'custom') {
                    \Illuminate\Support\Facades\Storage::disk('private')->delete($user->avatar_source);
                }

                $user->update([
                    'avatar_type' => 'custom',
                    'avatar_source' => $path
                ]);
            
            // CASO B: Selección de Icono
            } else {
                $user->update([
                    'avatar_type' => 'icon',
                    'avatar_source' => $request->avatar_source
                ]);
            }

            return back()->with('success', 'Foto actualizada correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error Avatar: ' . $e->getMessage());
            // Mensaje amigable al usuario, pero logueamos el error técnico
            return back()->with('error', 'No se pudo procesar la imagen. Intenta con una más pequeña.');
        }
    }
}