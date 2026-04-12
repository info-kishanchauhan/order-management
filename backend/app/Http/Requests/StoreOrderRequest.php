<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'min:2', 'max:100'],
            'item_name' => ['required', 'string', 'min:2', 'max:150'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', Rule::in(OrderStatus::values())],
        ];
    }
}
