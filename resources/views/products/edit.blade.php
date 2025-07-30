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

            <!-- Color Variants Section -->
            <div class="form-group">
                <label>Color Variants</label>
                <div id="color-variants-container">
                    @if(old('color_variants', $product->colorVariants))
                        @foreach(old('color_variants', $product->colorVariants) as $index => $variant)
                            <div class="row color-variant-row mb-2">
                                <div class="col-md-5">
                                    <input type="text" name="color_variants[{{ $index }}][color]" class="form-control" placeholder="Color (e.g., Red, Blue, or leave empty for 'No Color')" value="{{ is_array($variant) ? $variant['color'] : $variant->color }}">
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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="component_category_filter">Filter Components by Category</label>
                        <select id="component_category_filter" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="components-container">
                    @if(old('components', $product->components))
                        @foreach(old('components', $product->components) as $index => $component)
                            <div class="row component-row mb-2">
                                <div class="col-md-6">
                                    <select name="components[{{ $index }}][component_product_id]" class="form-control component-product-select" required>
                                        <option value="">Select Component Product</option>
                                        @foreach($simpleProducts as $simpleProduct)
                                            <option value="{{ $simpleProduct->id }}" data-category="{{ $simpleProduct->category_id }}" {{ (is_array($component) ? $component['component_product_id'] : $component->component_product_id) == $simpleProduct->id ? 'selected' : '' }}>{{ $simpleProduct->name }} ({{ $simpleProduct->category->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="components[{{ $index }}][quantity_needed]" class="form-control" placeholder="Quantity Needed" value="{{ is_array($component) ? $component['quantity_needed'] : $component->quantity_needed }}" required min="1">
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

        // Initialize simpleProducts first
        var simpleProducts = {!! json_encode($simpleProducts->toArray()) !!};
        console.log('Simple Products:', simpleProducts); // Debug log

        // Function to check component stock availability
        function validateComponentStock() {
            let isValid = true;
            let errorMessages = [];
            
            // Only validate if it's a composite product
            if (!$('#is_composite').prop('checked')) {
                return true;
            }

            $('.component-row').each(function() {
                const $row = $(this);
                const $select = $row.find('select[name*="component_product_id"]');
                const $quantity = $row.find('input[name*="quantity_needed"]');
                
                const componentId = $select.val();
                const quantityNeeded = parseInt($quantity.val()) || 0;
                
                if (componentId && quantityNeeded > 0) {
                    const component = simpleProducts.find(p => p.id == componentId);
                    if (component) {
                        // Calculate total available stock from all color variants
                        const totalStock = component.color_variants.reduce((sum, variant) => sum + (parseInt(variant.quantity) || 0), 0);
                        
                        if (totalStock < quantityNeeded) {
                            isValid = false;
                            errorMessages.push(`Not enough stock for component: ${component.name}. Available: ${totalStock}, Required: ${quantityNeeded}`);
                            $quantity.addClass('is-invalid');
                        } else {
                            $quantity.removeClass('is-invalid');
                        }
                    }
                }
            });

            if (!isValid) {
                alert('Stock Validation Failed:\n' + errorMessages.join('\n'));
            }
            
            return isValid;
        }

        // Add form submit handler for validation
        $('form').on('submit', function(e) {
            if (!validateComponentStock()) {
                e.preventDefault();
                return false;
            }
        });

        // Composite product fields
        function addNewComponentRow() {
            try {
                console.log('Adding new component row...'); // Debug log
                var componentIndex = $('#components-container .component-row').length;
                console.log('Component index:', componentIndex); // Debug log
                
                var selectedCategory = $('#component_category_filter').val();
                console.log('Selected category:', selectedCategory); // Debug log
                
                // Ensure simpleProducts is defined and has data
                if (!simpleProducts || !Array.isArray(simpleProducts)) {
                    console.error('Simple products data is not properly initialized:', simpleProducts);
                    return;
                }

                var filteredProducts = simpleProducts.filter(p => selectedCategory === '' || p.category_id == selectedCategory);
                console.log('Filtered products:', filteredProducts); // Debug log

                var productOptions = filteredProducts
                    .map(p => {
                        // Safely access category name
                        const categoryName = p.category ? p.category.name : '';
                        // Calculate total available stock
                        const totalStock = p.color_variants ? p.color_variants.reduce((sum, variant) => sum + (parseInt(variant.quantity) || 0), 0) : 0;
                        return `<option value="${p.id}" data-category="${p.category_id}" data-stock="${totalStock}">${p.name} ${categoryName ? `(${categoryName})` : ''} - Stock: ${totalStock}</option>`;
                    })
                    .join('');

                var newRow = `
                    <div class="row component-row mb-2">
                        <div class="col-md-6">
                            <select name="components[${componentIndex}][component_product_id]" class="form-control component-product-select" required>
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
                `;

                $('#components-container').append(newRow);
                console.log('New row added successfully'); // Debug log
            } catch (error) {
                console.error('Error adding component row:', error);
            }
        }

        $('#is_composite').change(function() {
            $('#components-wrapper').toggleClass('d-none', !this.checked);
            
            // Add initial component row if switching to composite and no components exist
            if (this.checked && $('#components-container .component-row').length === 0) {
                addNewComponentRow();
            }
        });

        // Component category filtering
        $('#component_category_filter').change(function() {
            var selectedCategory = $(this).val();
            filterComponentOptions(selectedCategory);
        });

        // Add quantity change handler to validate stock
        $(document).on('change', 'input[name*="quantity_needed"]', function() {
            const $input = $(this);
            const $select = $input.closest('.component-row').find('select[name*="component_product_id"]');
            const componentId = $select.val();
            const quantityNeeded = parseInt($input.val()) || 0;

            if (componentId && quantityNeeded > 0) {
                const component = simpleProducts.find(p => p.id == componentId);
                if (component) {
                    const totalStock = component.color_variants.reduce((sum, variant) => sum + (parseInt(variant.quantity) || 0), 0);
                    if (totalStock < quantityNeeded) {
                        $input.addClass('is-invalid');
                        alert(`Warning: Not enough stock for ${component.name}. Available: ${totalStock}, Required: ${quantityNeeded}`);
                    } else {
                        $input.removeClass('is-invalid');
                    }
                }
            }
        });

        function filterComponentOptions(categoryId) {
            $('.component-product-select').each(function() {
                var $select = $(this);
                var selectedValue = $select.val();
                
                $select.find('option').each(function() {
                    var $option = $(this);
                    if ($option.val() === '') {
                        $option.show(); // Always show "Select Component Product" option
                        return;
                    }
                    
                    var optionCategory = $option.data('category');
                    if (categoryId === '' || optionCategory == categoryId) {
                        $option.show();
                    } else {
                        $option.hide();
                        if ($option.val() === selectedValue) {
                            $select.val(''); // Clear selection if hidden
                        }
                    }
                });
            });
        }

        // Add component button handler
        $(document).on('click', '#add-component-btn', function(e) {
            e.preventDefault();
            console.log('Add component button clicked'); // Debug log
            addNewComponentRow();
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
                        <input type="text" name="color_variants[${variantIndex}][color]" class="form-control" placeholder="Color (e.g., Red, Blue, or leave empty for 'No Color')">
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
