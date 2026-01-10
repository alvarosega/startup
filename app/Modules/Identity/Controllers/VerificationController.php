<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validación estricta de múltiples archivos
        $request->validate([
            'front_ci' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'back_ci'  => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'selfie'   => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            // 2. Almacenamiento organizado por ID de usuario
            $frontPath = $request->file('front_ci')->store("verifications/{$user->id}", 'public');
            $backPath  = $request->file('back_ci')->store("verifications/{$user->id}", 'public');
            $selfiePath = $request->file('selfie')->store("verifications/{$user->id}", 'public');

            // 3. Crear o actualizar la solicitud de verificación
            $user->verifications()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'front_ci_path' => $frontPath,
                    'back_ci_path'  => $backPath,
                    'selfie_path'   => $selfiePath,
                    'status'        => 'pending' // Estado inicial para auditoría
                ]
            );

            return back()->with('message', 'Documentos enviados para revisión.');
        });
    }
}