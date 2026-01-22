<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade; // Facade para validar inputs
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->load('profile');
        $profile = $user->profile;
        
        // 1. ESTADO: ¿Tiene documentos subidos?
        $hasDocuments = $profile->ci_front_path && $profile->license_photo_path;
        
        // 2. ESTADO: ¿Está verificado por Admin?
        $isVerified = $profile->is_identity_verified;

        // Si ya está verificado, cargamos pedidos (Lógica futura)
        $pendingOrders = []; 

        return Inertia::render('Driver/Dashboard', [
            'auth_status' => [
                'has_documents' => (bool)$hasDocuments,
                'is_verified'   => (bool)$isVerified,
                'rejection_reason' => null // Podríamos añadir esto a futuro si lo rechazan
            ],
            'pendingOrders' => $pendingOrders
        ]);
    }

    // NUEVO MÉTODO: Subir Documentos
    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'ci_front' => 'required|image|max:5120', // Max 5MB
            'license_photo' => 'required|image|max:5120',
            // Opcionales
            'vehicle_photo' => 'nullable|image|max:5120',
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        // Guardar archivos en disco 'public' (o 'private' si quieres mas seguridad)
        if ($request->hasFile('ci_front')) {
            $profile->ci_front_path = $request->file('ci_front')->store('drivers/docs', 'public');
        }
        if ($request->hasFile('license_photo')) {
            $profile->license_photo_path = $request->file('license_photo')->store('drivers/docs', 'public');
        }
        if ($request->hasFile('vehicle_photo')) {
            $profile->vehicle_photo_path = $request->file('vehicle_photo')->store('drivers/docs', 'public');
        }

        $profile->save();

        return back()->with('success', 'Documentos enviados a revisión.');
    }

    public function history()
    {
        return Inertia::render('Driver/History');
    }
}