<?php

declare(strict_types=1);

namespace App\Actions\Admin\Operations\Provider;

use App\Models\Operations\Provider;

class ListProviders
{
    /**
     * Ejecuta búsquedas condicionales y mapea el cursor directamente a estructuras de arreglos planos nativos.
     */
    public function execute(?string $search = null): array
    {
        $paginator = Provider::query()
            ->when($search, function ($query, $search) {
                $term = "{$search}%"; // Indexación B-Tree optimizada mediante prefijo puro en MySQL 8
                $query->where(function ($sub) use ($term) {
                    $sub->where('company_name', 'like', $term)
                        ->orWhere('commercial_name', 'like', $term)
                        ->orWhere('tax_id', 'like', $term)
                        ->orWhere('internal_code', 'like', $term);
                });
            })
            ->orderBy('company_name', 'asc')
            ->cursorPaginate(15);

        $items = [];
        foreach ($paginator->items() as $provider) {
            $items[] = [
                'id' => (string) $provider->id,
                'company_name' => (string) $provider->company_name,
                'commercial_name' => $provider->commercial_name ? (string) $provider->commercial_name : null,
                'slug' => (string) $provider->slug,
                'tax_id' => (string) $provider->tax_id,
                'internal_code' => $provider->internal_code ? (string) $provider->internal_code : null,
                'contact_name' => $provider->contact_name ? (string) $provider->contact_name : null,
                'email_orders' => $provider->email_orders ? (string) $provider->email_orders : null,
                'phone' => $provider->phone ? (string) $provider->phone : null,
                'address' => $provider->address ? (string) $provider->address : null,
                'city' => $provider->city ? (string) $provider->city : null,
                'lead_time_days' => (int) $provider->lead_time_days,
                'min_order_value' => (float) $provider->min_order_value,
                'credit_days' => (int) $provider->credit_days,
                'credit_limit' => (float) $provider->credit_limit,
                'is_active' => (bool) $provider->is_active,
                'notes' => $provider->notes ? (string) $provider->notes : null,
            ];
        }

        return [
            'items' => $items,
            'meta'  => [
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
            ]
        ];
    }
}