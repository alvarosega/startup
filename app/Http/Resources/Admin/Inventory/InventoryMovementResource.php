<?php

namespace App\Http\Resources\Admin\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->id,
            'type'            => (string) $this->type,
            'quantity'        => (int) $this->quantity,
            'unit_cost'       => (float) $this->unit_cost,
            'running_balance' => (int) ($this->running_balance ?? 0),
            
            // Sanitización UTF-8 Obligatoria
            'reference'       => $this->sanitizeUTF8($this->reference),
            
            // Relaciones
            'lot_code'        => $this->lot?->lot_code ?? 'S/N',
            'branch_name'     => $this->branch?->name ?? 'N/A',
            'admin_name'      => $this->admin ? "{$this->admin->first_name} {$this->admin->last_name}" : 'Sistema',
            
            'created_at'      => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    protected function sanitizeUTF8(?string $text): ?string
    {
        if (!$text) return null;
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }
}