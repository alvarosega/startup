<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class GlobalUniqueIdentity implements ValidationRule
{
    public function __construct(protected ?string $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $silos = [
            'admins' => 'App\Models\Users\Admin',
            'customers' => 'App\Models\Users\Customer',
            'drivers' => 'App\Models\Users\Driver'
        ];
        
        foreach ($silos as $table => $modelClass) {
            $query = DB::table($table)->where($attribute, $value);
            
            if ($this->ignoreId) {
                $query->where('id', '!=', $this->ignoreId);
            }

            $record = $query->first();

            if (!$record) {
                continue;
            }

            // Corrección: Tanto 'drivers' como 'customers' manejan SoftDeletes y deleted_epoch
            if (in_array($table, ['drivers', 'customers'], true)) {
                if (!is_null($record->deleted_at) || (int)$record->deleted_epoch > 0) {
                    
                    // Extraer el último motivo de eliminación desde la tabla de auditoría
                    $latestLog = DB::table('audit_logs')
                        ->where('target_type', $modelClass)
                        ->where('target_id', $record->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $reason = 'No especificado';
                    if ($latestLog) {
                        $payload = json_decode($latestLog->payload_before ?? '{}', true);
                        $reason = $payload['rejection_reason'] ?? $latestLog->action;
                    }

                    $fail("El " . ($attribute === 'email' ? 'correo' : 'teléfono') . " pertenece a una cuenta que fue eliminada el {$record->deleted_at}. Motivo registrado: {$reason}.");
                    return;
                }
            }

            // Si encuentra el registro y no está eliminado de forma lógica
            $fail("Este " . ($attribute === 'email' ? 'correo' : 'teléfono') . " ya está registrado y activo en el sistema.");
            return;
        }
    }
}