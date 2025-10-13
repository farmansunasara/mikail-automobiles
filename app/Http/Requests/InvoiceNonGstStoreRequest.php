<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceNonGstStoreRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date|before_or_equal:today',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'discount_type' => 'required|in:0,1',
            'discount_value' => 'required|numeric|min:0',
            'packaging_fees' => 'nullable|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.price' => 'required|numeric|min:0|max:999999.99',
            'items.*.variants' => 'required|array|min:1',
            'items.*.variants.*.product_id' => 'required|exists:product_color_variants,id',
            'items.*.variants.*.quantity' => 'required|integer|min:0|max:999999',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer selection is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'invoice_date.required' => 'Invoice date is required.',
            'invoice_date.date' => 'Invoice date must be a valid date.',
            'invoice_date.before_or_equal' => 'Invoice date cannot be in the future.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be on or after the invoice date.',
            'discount_type.required' => 'Discount type is required.',
            'discount_type.in' => 'Discount type must be either percentage or fixed amount.',
            'discount_value.required' => 'Discount value is required.',
            'discount_value.numeric' => 'Discount value must be a valid number.',
            'discount_value.min' => 'Discount value cannot be negative.',
            'packaging_fees.numeric' => 'Packaging fees must be a valid number.',
            'packaging_fees.min' => 'Packaging fees cannot be negative.',
            'packaging_fees.max' => 'Packaging fees cannot exceed 999,999.99.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.price.required' => 'Price is required for each item.',
            'items.*.price.numeric' => 'Price must be a valid number.',
            'items.*.price.min' => 'Price cannot be negative.',
            'items.*.variants.required' => 'At least one variant is required for each item.',
            'items.*.variants.min' => 'At least one variant is required for each item.',
            'items.*.variants.*.product_id.required' => 'Product selection is required.',
            'items.*.variants.*.product_id.exists' => 'Selected product variant does not exist.',
            'items.*.variants.*.quantity.required' => 'Quantity is required.',
            'items.*.variants.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.variants.*.quantity.min' => 'Quantity cannot be negative.',
            'items.*.variants.*.quantity.max' => 'Quantity cannot exceed 999,999.',
        ];
    }
}

