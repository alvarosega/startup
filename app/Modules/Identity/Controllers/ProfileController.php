<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Muestra la pantalla de edición de perfil.
     */
    public function edit()
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user()->load('profile'),
            'status' => session('status'),
        ]);
    }

    /**
     * Procesa la actualización de los datos del perfil (Nivel 2).
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        \Log::info('--- INICIO UPDATE PERFIL ---');
    
        try {
            return DB::transaction(function () use ($request, $user) {
                // 1. Actualizar Email (Solo si se envió y no es nulo)
                if ($request->filled('email')) {
                    $user->update(['email' => $request->email]);
                    \Log::info('Email actualizado en users');
                }
    
                // 2. Limpiar datos: Convertir strings vacíos a null (Vital para 'gender')
                $profileData = $request->safe()->except(['email']);
                
                // Forzamos que si el campo viene vacío, se guarde como null en la DB
                foreach ($profileData as $key => $value) {
                    if ($value === '') $profileData[$key] = null;
                }
    
                // 3. Guardar en UserProfile
                $profile = $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
    
                \Log::info('PERFIL GUARDADO EXITOSAMENTE ID: ' . $profile->id);
                return back()->with('message', '¡Nivel completado!');
            });
        } catch (\Throwable $e) {
            // ESTE LOG TE DIRÁ EL ERROR REAL (ej: "Column not found" o "Data too long")
            \Log::error('ERROR CRÍTICO AL GUARDAR: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'No se pudo guardar el perfil: ' . $e->getMessage()]);
        }
    }
}