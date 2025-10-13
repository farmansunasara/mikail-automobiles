<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:products,name,NULL,id,category_id,' . $this->category_id . ',subcategory_id,' . $this->subcategory_id,
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'price' => 'required|numeric|min:0|max:999999.99',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'is_composite' => 'boolean',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color_variants' => 'required|array|min:1',
            'color_variants.*.color' => 'required|string|max:100',
            'color_variants.*.quantity' => 'required|integer|min:0|max:999999',
            'color_variants.*.minimum_threshold' => 'nullable|integer|min:0|max:999999',
            'color_variants.*.color_id' => 'nullable|exists:colors,id',
            'color_variants.*.color_usage_grams' => 'nullable|numeric|min:0|max:999999',
            'components' => 'required_if:is_composite,true|array',
            'components.*.component_product_id' => 'required_if:is_composite,true|exists:products,id',
            'components.*.quantity_needed' => 'required_if:is_composite,true|integer|min:1|max:999',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.unique' => 'A product with this name already exists in this category and subcategory.',
            'category_id.required' => 'Category selection is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'subcategory_id.required' => 'Subcategory selection is required.',
            'subcategory_id.exists' => 'Selected subcategory does not exist.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'gst_rate.numeric' => 'GST rate must be a valid number.',
            'gst_rate.min' => 'GST rate cannot be negative.',
            'gst_rate.max' => 'GST rate cannot exceed 100%.',
            'color_variants.required' => 'At least one color variant is required.',
            'color_variants.min' => 'At least one color variant is required.',
            'color_variants.*.color.required' => 'Color name is required for each variant.',
            'color_variants.*.quantity.required' => 'Quantity is required for each variant.',
            'color_variants.*.quantity.integer' => 'Quantity must be a whole number.',
            'color_variants.*.quantity.min' => 'Quantity cannot be negative.',
            'components.required_if' => 'Components are required for composite products.',
            'components.*.component_product_id.required_if' => 'Component product selection is required.',
            'components.*.component_product_id.exists' => 'Selected component product does not exist.',
            'components.*.quantity_needed.required_if' => 'Quantity needed is required for each component.',
            'components.*.quantity_needed.integer' => 'Quantity needed must be a whole number.',
            'components.*.quantity_needed.min' => 'Quantity needed must be at least 1.',
        ];
    }
}
