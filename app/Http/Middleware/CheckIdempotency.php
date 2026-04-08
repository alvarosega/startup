<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class CheckIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (!$idempotencyKey) {
            abort(400, 'X-Idempotency-Key header is strictly required.');
        }

        $existingRecord = DB::table('idempotency_keys')
            ->where('key', $idempotencyKey)
            ->first();

        if ($existingRecord) {
            if ($existingRecord->response_code) {
                // RECTIFICACIÓN: Retornar respuesta cruda con el código original.
                // Esto maneja tanto JSON como Redirecciones HTML.
                return response($existingRecord->response_body, $existingRecord->response_code)
                    ->header('Content-Type', $existingRecord->response_code === 302 ? 'text/html' : 'application/json')
                    ->header('X-Idempotency-Replayed', 'true');
            }
            abort(409, 'Conflict: Transaction is currently processing.');
        }

        // Bloqueo atómico
        DB::table('idempotency_keys')->insert([
            'id' => (string) Uuid::v7(),
            'key' => $idempotencyKey,
            'customer_id' => auth()->guard('customer')->id(),
            'request_path' => $request->path(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $next($request);

        // RECTIFICACIÓN: Solo actualizar si la respuesta es final (no excepciones sin manejar)
        DB::table('idempotency_keys')
            ->where('key', $idempotencyKey)
            ->update([
                'response_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(), // Se guarda como String puro
                'updated_at' => now(),
            ]);

        return $response;
    }
}