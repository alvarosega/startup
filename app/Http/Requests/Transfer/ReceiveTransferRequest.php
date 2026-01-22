<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveTransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:transfer_items,id', // ID del item de la transferencia
            'items.*.qty_received' => 'required|integer|min:0', // Puede recibir 0 si se rompió todo
        ];
    }
    
    // Validamos que no reciba más de lo enviado
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->items as $item) {
                $transferItem = \App\Models\TransferItem::find($item['id']);
                if ($transferItem && $item['qty_received'] > $transferItem->qty_sent) {
                    $validator->errors()->add('items', "No puedes recibir más de lo enviado en el item {$transferItem->sku->name}.");
                }
            }
        });
    }
}