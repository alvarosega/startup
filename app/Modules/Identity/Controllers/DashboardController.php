<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ---------------------------------------------------------
        // 1. INVENTORY MANAGER (Panel Operativo)
        // ---------------------------------------------------------
        if ($user->hasRole('inventory_manager')) {
            
            // Datos específicos para el operador: Bajas pendientes y nombre de sucursal
            $pendingRemovals = 0;
            $branchName = 'Sin Sucursal Asignada';

            if ($user->branch_id) {
                $pendingRemovals = DB::table('removal_requests')
                    ->where('branch_id', $user->branch_id)
                    ->where('status', 'pending')
                    ->count();
                
                $branchName = Branch::find($user->branch_id)?->name ?? 'Sucursal Eliminada';
            }

            // RUTA CORREGIDA: Apunta a resources/js/Pages/Admin/Dashboards/Inventory.vue
            return Inertia::render('Admin/Dashboards/Inventory', [
                'pending_removals' => $pendingRemovals,
                'my_branch' => $branchName
            ]);
        }

        // ---------------------------------------------------------
        // 2. LOGISTICS MANAGER (Panel de Flota/Catálogo)
        // ---------------------------------------------------------
        if ($user->hasRole('logistics_manager')) {
            // Si aún no creas Admin/Dashboards/Logistics.vue, fallará. 
            // Asegúrate de crearlo o usa SuperAdmin temporalmente.
            return Inertia::render('Admin/Dashboards/Logistics', [ 
                'active_drivers' => User::role('driver')->where('is_active', true)->count(),
            ]);
        }

        // ---------------------------------------------------------
        // 3. IDENTITY AUDITOR (Panel de KYC)
        // ---------------------------------------------------------
        if ($user->hasRole('identity_auditor')) {
             return Inertia::render('Admin/Dashboards/IdentityAuditor', [
                'pending_verifications' => DB::table('user_verifications')
                    ->where('status', 'pending')
                    ->count()
            ]);
        }

        // ---------------------------------------------------------
        // 4. SUPER ADMIN / BRANCH ADMIN (Panel Gerencial - Default)
        // ---------------------------------------------------------
        
        // RUTA CORREGIDA: Apunta a resources/js/Pages/Admin/Dashboards/SuperAdmin.vue
        return Inertia::render('Admin/Dashboards/SuperAdmin', [
            'stats' => [
                'total_users' => User::count(),
                'pending_verifications' => DB::table('user_verifications')->where('status', 'pending')->count(),
                'active_branches' => Branch::where('is_active', true)->count(),
                // Agrega aquí datos financieros si los necesitas
            ]
        ]);
    }
}