<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
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
            'order_date' => 'required|date|before_or_equal:today',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:categories,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.price' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.variants' => 'required|array|min:1',
            'items.*.variants.*.product_id' => 'required|exists:product_color_variants,id',
            'items.*.variants.*.quantity' => 'required|integer|min:1|max:999999',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This filters out incomplete items and variants (e.g. zero-quantity variants)
     * so the validation rules (which require items.*.*.* fields) don't fail
     * for partially filled JS-driven forms. The controller also does a similar
     * filtering but FormRequest validation runs before the controller method,
     * so we must sanitize here.
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        $filteredItems = [];

        foreach ($items as $item) {
            if (!isset($item['product_id']) || !isset($item['price']) || !isset($item['variants']) || empty($item['variants'])) {
                continue;
            }

            $filteredVariants = [];
            foreach ($item['variants'] as $variant) {
                if (isset($variant['product_id']) && isset($variant['quantity']) && intval($variant['quantity']) > 0) {
                    $filteredVariants[] = $variant;
                }
            }

            if (!empty($filteredVariants)) {
                $item['variants'] = $filteredVariants;
                $filteredItems[] = $item;
            }
        }

        $this->merge(['items' => $filteredItems]);
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer selection is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'order_date.required' => 'Order date is required.',
            'order_date.date' => 'Order date must be a valid date.',
            'order_date.before_or_equal' => 'Order date cannot be in the future.',
            'delivery_date.date' => 'Delivery date must be a valid date.',
            'delivery_date.after_or_equal' => 'Delivery date must be on or after the order date.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.category_id.required' => 'Category selection is required for each item.',
            'items.*.category_id.exists' => 'Selected category does not exist.',
            'items.*.product_id.required' => 'Product selection is required for each item.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.price.required' => 'Price is required for each item.',
            'items.*.price.numeric' => 'Price must be a valid number.',
            'items.*.price.min' => 'Price must be at least 0.01.',
            'items.*.price.max' => 'Price cannot exceed 999,999.99.',
            'items.*.variants.required' => 'At least one variant is required for each item.',
            'items.*.variants.min' => 'At least one variant is required for each item.',
            'items.*.variants.*.product_id.required' => 'Product variant selection is required.',
            'items.*.variants.*.product_id.exists' => 'Selected product variant does not exist.',
            'items.*.variants.*.quantity.required' => 'Quantity is required.',
            'items.*.variants.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.variants.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.variants.*.quantity.max' => 'Quantity cannot exceed 999,999.',
        ];
    }
}
