<?php

namespace App\Http\Requests\Inventory;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('inventory.update') ?? false;
    }

    public function rules(TenantContext $tenantContext): array
    {
        $businessId = $tenantContext->getCurrentBusinessId();
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->where('business_id', $businessId)->ignore($categoryId)->withoutTrashed(),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
