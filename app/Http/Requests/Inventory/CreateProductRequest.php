<?php

namespace App\Http\Requests\Inventory;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('inventory.create') ?? false;
    }

    public function rules(TenantContext $tenantContext): array
    {
        $businessId = $tenantContext->getCurrentBusinessId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'opening_stock' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('business_id', $businessId),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products')->where('business_id', $businessId)->withoutTrashed(),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products')->where('business_id', $businessId)->withoutTrashed(),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'track_inventory' => ['nullable', 'boolean'],
        ];
    }
}
