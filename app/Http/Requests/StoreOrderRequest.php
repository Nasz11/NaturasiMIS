<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'client_id'            => 'nullable|integer',
            'client_name'          => 'required|string|max:255',
            'order_date'           => 'required|date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product'      => 'required|string',
            'items.*.variant_id'   => 'required|integer|exists:product_variants,id',
            'items.*.quantity_pcs' => 'required|numeric|min:1',
        ];
    }
}
