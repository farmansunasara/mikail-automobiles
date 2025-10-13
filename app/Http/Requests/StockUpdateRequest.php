<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'color_variant_id' => 'required|exists:product_color_variants,id',
            'quantity' => 'required|integer|min:0|max:999999',
            'change_type' => 'required|in:inward,outward',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product selection is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'color_variant_id.required' => 'Color variant selection is required.',
            'color_variant_id.exists' => 'Selected color variant does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'quantity.max' => 'Quantity cannot exceed 999,999.',
            'change_type.required' => 'Stock movement type is required.',
            'change_type.in' => 'Stock movement type must be either inward or outward.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }
}

