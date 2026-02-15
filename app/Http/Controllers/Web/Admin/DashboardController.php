<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Admin;
use App\Models\Customer; // <--- ERROR REAL: Faltaba esta línea
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboards/SuperAdmin', [
            'stats' => [
                'total_users'           => 0,
                'total_products'        => 0,
                'active_branches'       => 0,
                'pending_verifications' => 0,
                'active_orders'         => 0,
            ]
        ]);
    }
}