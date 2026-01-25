<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// Arquitectura
use App\DTOs\Driver\DriverProfileData;
use App\DTOs\Driver\DriverDocumentsData;
use App\Http\Requests\Driver\UpdateDriverProfileRequest;
use App\Http\Requests\Driver\UploadDocumentsRequest;
use App\Actions\Driver\UpdateDriverProfile;
use App\Actions\Driver\UpdateDriverDocuments;
use App\Http\Resources\DriverProfileResource;

class DriverController extends Controller
{
    /**
     * Dashboard Principal
     * Muestra estado, ruta actual O formulario de subida de docs si falta.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Cargar relación driverProfile para verificar estado y documentos
        $user->load('driverProfile');
        $driver = $user->driverProfile;

        return Inertia::render('Driver/Dashboard', [
            'driver' => $driver, // La vista Dashboard.vue usa esto para v-if="!driver.has_documents"
            'pendingOrders' => [] // Aquí cargarás las órdenes asignadas en el futuro
        ]);
    }

    /**
     * Vista para Editar Perfil (Datos del Vehículo)
     */
    public function editProfile()
    {
        $user = Auth::user();
        $driver = $user->driverProfile;

        return Inertia::render('Driver/Profile/Edit', [
            'driver' => $driver
        ]);
    }

    /**
     * Actualizar Datos del Vehículo (Texto)
     */
    public function updateProfile(UpdateDriverProfileRequest $request, UpdateDriverProfile $action)
    {
        try {
            $data = DriverProfileData::fromRequest($request);
            $action->execute($request->user(), $data);

            return back()->with('success', 'Información del vehículo actualizada.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar perfil.']);
        }
    }

    /**
     * Subir Documentos (Imágenes) - Usado desde el Dashboard
     */
    public function uploadDocuments(UploadDocumentsRequest $request, UpdateDriverDocuments $action)
    {
        // Nota: Asegúrate de tener DriverDocumentsData y UpdateDriverDocuments creados previamente
        // Si no los tienes, avísame para dártelos.
        $data = \App\DTOs\Driver\DriverDocumentsData::fromRequest($request);
        $action->execute($request->user(), $data);

        return back()->with('success', 'Documentos enviados a revisión.');
    }

    public function history()
    {
        return Inertia::render('Driver/History');
    }
    // --- NUEVO: Ver Perfil Completo ---
    public function indexProfile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $user->load(['driverProfile', 'profile']); 

        // CORRECCIÓN CRÍTICA: Usar ->resolve() para quitar el envoltorio 'data'
        $driverData = $user->driverProfile 
            ? (new \App\Http\Resources\DriverProfileResource($user->driverProfile))->resolve()
            : null;

        return Inertia::render('Driver/Profile/Index', [
            'driver' => $driverData, // Ahora enviamos el array limpio
            'user_profile' => $user->profile, 
            'email' => $user->email,
            'phone' => $user->phone
        ]);
    }
}