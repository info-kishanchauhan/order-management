<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['created_at', 'price_cents', 'status', 'customer_name', 'item_name', 'order_code'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(OrderStatus::values())],
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('q') && $this->input('q') === '') {
            $this->merge(['q' => null]);
        }
        if ($this->has('status') && $this->input('status') === '') {
            $this->merge(['status' => null]);
        }
    }
}
