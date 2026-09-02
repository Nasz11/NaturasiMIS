<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'items'                => 'required|array|min:1',
            'items.*.product'      => 'required|string',
            'items.*.variant_id'   => 'required|numeric',
            'items.*.quantity_pcs' => 'required|numeric|min:0.001',
        ];
    }
}
