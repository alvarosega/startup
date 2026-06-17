<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class TransferReceptionViewController extends Controller
{
    public function index(string $id): Response
    {
        // Despacha la pantalla de recepción inyectando el ID de control de la transferencia
        return Inertia::render('Admin/Inventory/TransferReceptionForm', [
            'transferId' => $id
        ]);
    }
}