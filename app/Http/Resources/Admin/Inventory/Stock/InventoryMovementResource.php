<?php

namespace App\Http\Resources\Admin\Inventory\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class InventoryMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => (string) $this->id,
            'type'             => (string) $this->type,
            'quantity'         => (int) $this->quantity,
            'unit_cost'        => (float) $this->unit_cost,
            'running_balance'  => (int) ($this->running_balance ?? 0),
            'reference'        => $this->sanitizeUTF8($this->reference),
            
            'lot_code'         => $this->lot?->lot_code ?? 'S/N',
            'branch_name'      => $this->branch?->name ?? 'N/A',
            'admin_name'       => $this->admin ? "{$this->admin->first_name} {$this->admin->last_name}" : 'Sistema',
            
            // Blindaje: Si el cast del modelo falla, parseamos manualmente
            'created_at'       => $this->created_at instanceof Carbon 
                                    ? $this->created_at->format('Y-m-d H:i:s') 
                                    : Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    protected function sanitizeUTF8(?string $text): ?string
    {
        if (!$text) return null;
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }
}