<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/api/geo/reverse', function (Request $request) {
    return Http::withHeaders(['User-Agent' => 'ElectricLuxury/1.0'])
        ->get("https://nominatim.openstreetmap.org/reverse", [
            'format' => 'json',
            'lat' => $request->lat,
            'lon' => $request->lng,
            'accept-language' => 'es'
        ])->json();
})->name('geo.reverse');

if (app()->environment('local')) {
    Route::get('/debug-auth', function () {
         $customer = Auth::guard('customer')->user();
         return response()->json([
             'status' => $customer ? 'LOGUEADO' : 'GUEST',
             'id' => $customer?->id
         ]);
    });
}