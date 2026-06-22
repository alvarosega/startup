<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Operations\Provider;
use App\Models\Operations\Branch;
use App\Models\Catalog\Sku;
use App\Http\Resources\Admin\Inventory\PurchaseResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PurchaseIntakeViewController extends Controller
{
    /**
     * Renderiza el historial de ingestas logísticas (Index).
     */
    public function index(Request $request): Response
    {
        $purchases = Purchase::with(['branch', 'provider', 'admin'])
            ->where('deleted_epoch', 0)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(15);

        return Inertia::render('Admin/Purchases/Index', [
            'purchases' => [
                'items' => PurchaseResource::collection($purchases->items())->resolve(),
                'meta'  => [
                    'next_cursor' => $purchases->nextCursor()?->encode(),
                    'prev_cursor' => $purchases->previousCursor()?->encode(),
                ]
            ]
        ]);
    }

    /**
     * Renderiza el formulario maestro-detalle para recepción de camiones (Create).
     */
    public function create(): Response
    {
        $providers = Provider::where('deleted_epoch', 0)->get(['id', 'company_name']);
        $branches = Branch::where('deleted_epoch', 0)->get(['id', 'name']);
        $skus = Sku::whereNull('deleted_at')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Purchases/Create', [
            'providers' => $providers->map(fn($p) => ['id' => $p->id, 'company_name' => mb_toUpperCase($p->company_name)]),
            'branches'  => $branches->map(fn($b) => ['id' => $b->id, 'name' => mb_toUpperCase($b->name)]),
            'skus'      => $skus->map(fn($s) => ['id' => $s->id, 'name' => mb_toUpperCase($s->name), 'code' => $s->code])
        ]);
    }
}