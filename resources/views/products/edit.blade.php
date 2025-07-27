@extends('layouts.admin')

@section('title', 'Edit Product')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Product: {{ $product->name }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        <small class="form-text text-muted">Base product name (colors will be managed separately below).</small>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="subcategory_id">Subcategory</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-control @error('subcategory_id') is-invalid @enderror" required>
                            <option value="">Select Category First</option>
                        </select>
                        @error('subcategory_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" name="price" id="price" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
                        @error('price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gst_rate">GST Rate (%)</label>
                        <input type="number" name="gst_rate" id="gst_rate" step="0.01" class="form-control @error('gst_rate') is-invalid @enderror" value="{{ old('gst_rate', $product->gst_rate) }}" required>
                        @error('gst_rate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hsn_code">HSN Code</label>
                        <input type="text" name="hsn_code" id="hsn_code" class="form-control @error('hsn_code') is-invalid @enderror" value="{{ old('hsn_code', $product->hsn_code) }}">
                        @error('hsn_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Color Variants Section -->
            <div class="form-group">
                <label>Color Variants</label>
                <div id="color-variants-container">
                    @if(old('color_variants', $product->colorVariants))
                        @foreach(old('color_variants', $product->colorVariants) as $index => $variant)
                            <div class="row color-variant-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" name="color_variants[{{ $index }}][color]" class="form-control" placeholder="Color (e.g., Red, Blue)" value="{{ is_array($variant) ? $variant['color'] : $variant->color }}" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" name="color_variants[{{ $index }}][quantity]" class="form-control" placeholder="Quantity" value="{{ is_array($variant) ? $variant['quantity'] : $variant->quantity }}" required min="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-variant-btn">Remove</button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row color-variant-row mb-2">
                            <div class="col-md-5">
                                <input type="text" name="color_variants[0][color]" class="form-control" placeholder="Color (e.g., Red, Blue)" required>
                            </div>
                            <div class="col-md-5">
                                <input type="number" name="color_variants[0][quantity]" class="form-control" placeholder="Quantity" required min="0">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-variant-btn">Remove</button>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" id="add-variant-btn" class="btn btn-sm btn-success">Add Color Variant</button>
                @error('color_variants') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="is_composite" value="0">
                    <input type="checkbox" name="is_composite" class="custom-control-input" id="is_composite" value="1" {{ old('is_composite', $product->is_composite) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_composite">Is Composite Product?</label>
                </div>
            </div>

            <div id="components-wrapper" class="{{ old('is_composite', $product->is_composite) ? '' : 'd-none' }}">
                <h4>Components</h4>
                <div id="components-container">
                    @if(old('components', $product->components))
                        @foreach(old('components', $product->components) as $index => $component)
                            <div class="row component-row mb-2">
                                <div class="col-md-6">
                                    <select name="components[{{ $index }}][component_product_id]" class="form-control" required>
                                        <option value="">Select Component Product</option>
                                        @foreach($simpleProducts as $simpleProduct)
                                            <option value="{{ $simpleProduct->id }}" {{ $component['component_product_id'] == $simpleProduct->id ? 'selected' : '' }}>{{ $simpleProduct->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="components[{{ $index }}][quantity_needed]" class="form-control" placeholder="Quantity Needed" value="{{ $component['quantity_needed'] }}" required min="1">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-component-btn">Remove</button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-component-btn" class="btn btn-sm btn-success">Add Component</button>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">Update Product</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Subcategory fetcher
        var initialCategoryId = $('#category_id').val();
        if(initialCategoryId) {
            fetchSubcategories(initialCategoryId, "{{ old('subcategory_id', $product->subcategory_id) }}");
        }
        $('#category_id').change(function() {
            fetchSubcategories($(this).val());
        });

        function fetchSubcategories(categoryId, selectedSubcategoryId = null) {
            if(categoryId) {
                $.ajax({
                    url: `/api/subcategories/${categoryId}`,
                    type: 'GET',
                    success: function(data) {
                        var subcategorySelect = $('#subcategory_id');
                        subcategorySelect.html('<option value="">Select Subcategory</option>');
                        $.each(data, function(key, value) {
                            var selected = selectedSubcategoryId == value.id ? 'selected' : '';
                            subcategorySelect.append(`<option value="${value.id}" ${selected}>${value.name}</option>`);
                        });
                    }
                });
            } else {
                $('#subcategory_id').html('<option value="">Select Category First</option>');
            }
        }

        // Composite product fields
        $('#is_composite').change(function() {
            $('#components-wrapper').toggleClass('d-none', !this.checked);
        });

        var simpleProducts = {!! json_encode($simpleProducts->toArray()) !!};

        $('#add-component-btn').click(function() {
            var componentIndex = $('#components-container .component-row').length;
            var productOptions = simpleProducts.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

            $('#components-container').append(`
                <div class="row component-row mb-2">
                    <div class="col-md-6">
                        <select name="components[${componentIndex}][component_product_id]" class="form-control" required>
                            <option value="">Select Component Product</option>
                            ${productOptions}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="components[${componentIndex}][quantity_needed]" class="form-control" placeholder="Quantity Needed" required min="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-component-btn">Remove</button>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove-component-btn', function() {
            $(this).closest('.component-row').remove();
        });

        // Color Variants functionality
        $('#add-variant-btn').click(function() {
            var variantIndex = $('#color-variants-container .color-variant-row').length;
            $('#color-variants-container').append(`
                <div class="row color-variant-row mb-2">
                    <div class="col-md-5">
                        <input type="text" name="color_variants[${variantIndex}][color]" class="form-control" placeholder="Color (e.g., Red, Blue)" required>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="color_variants[${variantIndex}][quantity]" class="form-control" placeholder="Quantity" required min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-variant-btn">Remove</button>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove-variant-btn', function() {
            if ($('#color-variants-container .color-variant-row').length > 1) {
                $(this).closest('.color-variant-row').remove();
            } else {
                alert('At least one color variant is required.');
            }
        });
    });
</script>
@endpush
