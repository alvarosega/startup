<?php

namespace App\Http\Controllers\Web\Customer\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Catalog\UpsertReviewRequest; // Crear este
use App\DTOs\Customer\Catalog\UpsertReviewDTO;
use App\Actions\Customer\Catalog\UpsertReviewAction;

class ReviewController extends Controller
{
    public function store(UpsertReviewRequest $request, string $productId, UpsertReviewAction $action)
    {
        $dto = UpsertReviewDTO::fromRequest($request, $productId);
        
        try {
            $action->execute($dto);
            return back()->with('success', 'Reseña procesada.');
        } catch (\Exception $e) {
            // Revelamos el error para debug, luego lo volverás a ocultar
            return back()->withErrors(['error' => 'LOG ERROR: ' . $e->getMessage()]);
        }
    }
}