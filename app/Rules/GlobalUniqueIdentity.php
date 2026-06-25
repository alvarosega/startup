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

            if (in_array($table, ['drivers', 'customers'], true)) {
                $hasDeletedAt = isset($record->deleted_at) && !is_null($record->deleted_at);
                $hasDeletedEpoch = isset($record->deleted_epoch) && (int) $record->deleted_epoch > 0;

                if ($hasDeletedAt || $hasDeletedEpoch) {
                    
                    $latestLog = DB::table('audit_logs')
                        ->where('target_type', $modelClass)
                        ->where('target_id', $record->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $reason = 'No especificado';
                    if ($latestLog) {
                        $payload = json_decode((string) ($latestLog->payload_before ?? '{}'), true);
                        $reason = $payload['rejection_reason'] ?? $latestLog->action;
                    }

                    $deletedDate = $record->deleted_at ?? date('Y-m-d H:i:s', (int) $record->deleted_epoch);
                    $fail("El " . ($attribute === 'email' ? 'correo' : 'teléfono') . " pertenece a una cuenta que fue eliminada el {$deletedDate}. Motivo registrado: {$reason}.");
                    return;
                }
            }

            // Si encuentra el registro y no está eliminado de forma lógica
            $fail("Este " . ($attribute === 'email' ? 'correo' : 'teléfono') . " ya está registrado y activo en el sistema.");
            return;
        }
    }
}