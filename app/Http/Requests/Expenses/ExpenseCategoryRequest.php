<?php

namespace App\Http\Requests\Expenses;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('expenses.create') ?? false;
    }

    public function rules(): array
    {
        $businessId = app(TenantContext::class)->getCurrentBusinessId();
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')
                    ->where('business_id', $businessId)
                    ->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
