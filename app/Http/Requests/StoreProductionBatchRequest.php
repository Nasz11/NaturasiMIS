<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'batch_number'    => 'required|string|unique:production_batches,batch_number',
            'product_type'    => 'required|string',
            'quantity'        => 'required|numeric|min:0.01',
            'production_date' => 'required|date',
            'status'          => 'required|in:In Production,Curing,Completed',
            'remarks'         => 'nullable|string',
            'staff_id'        => 'nullable|exists:users,id',
        ];
    }
}
