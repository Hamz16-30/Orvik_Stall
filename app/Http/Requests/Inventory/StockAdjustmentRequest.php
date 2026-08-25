<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return ($user?->isOwner() || $user?->isManager()) && $user->hasPermissionTo('inventory.update');
    }

    public function rules(): array
    {
        $type = strtoupper($this->input('type', ''));

        return [
            'type' => ['required', 'string', 'in:ADD,REMOVE,SET,ADJUSTMENT_ADD,ADJUSTMENT_REMOVE,ADJUSTMENT_SET'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'reason' => [
                in_array($type, ['SET', 'ADJUSTMENT_SET']) ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
