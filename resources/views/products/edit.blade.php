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
                <label><strong>Product Quantity & Colors</strong></label>
                <small class="form-text text-muted">
                    Manage color variants with their quantities and color usage per unit
                </small>
                
                <div id="color-variants-container">
                    <!-- Existing color variants will be loaded here -->
                </div>
                
                <div class="add-color-section mt-3 p-3" style="border: 2px dashed #dee2e6; border-radius: 4px;">
                    <h6>Add New Color Variant</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search & Select Color</label>
                                <input type="text" id="color-search" class="form-control" placeholder="Search colors..." autocomplete="off">
                                <div id="color-dropdown" class="dropdown-menu" style="display: none; width: 100%; max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <!-- Color options will be populated here -->
                                </div>
                                <small class="form-text text-muted">Type to search colors or enter custom color name</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>Quantity</label>
                            <input type="number" id="new-quantity" class="form-control" placeholder="Enter quantity" min="0">
                            <small class="form-text text-muted">Product quantity</small>
                        </div>
                        <div class="col-md-3">
                            <label>Color Usage (grams)</label>
                            <input type="number" id="color-usage-grams" class="form-control" placeholder="Color usage (grams)" min="0" step="0.01">
                            <small class="form-text text-muted">Grams per unit</small>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-success form-control" id="add-color-btn">
                                <i class="fas fa-plus"></i> Add Color
                            </button>
                        </div>
                    </div>
                </div>
                
                @error('color_variants') <span class="text-danger">{{ $message }}</span> @enderror
                @error('color_variants.*') <span class="text-danger">{{ $message }}</span> @enderror
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

        // Color search functionality
        let searchTimeout;
        let selectedColorId = null;
        let selectedColorName = '';
        let colorVariantIndex = 0;
        
        // Initialize color search
        initializeColorSearch();
        
        function initializeColorSearch() {
            const $colorSearch = $('#color-search');
            const $dropdown = $('#color-dropdown');
            
            // Load initial colors
            searchColors('');
            
            // Search on input
            $colorSearch.on('input', function() {
                const term = $(this).val();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchColors(term);
                }, 300);
            });
            
            // Show dropdown on focus
            $colorSearch.on('focus', function() {
                $dropdown.show();
            });
            
            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#color-search, #color-dropdown').length) {
                    $dropdown.hide();
                }
            });
        }
        
        function searchColors(term) {
            $.ajax({
                url: '/api/colors/search',
                type: 'GET',
                data: { 
                    term: term,
                    limit: 100
                },
                success: function(data) {
                    displayColorResults(data, term);
                },
                error: function() {
                    console.log('Failed to search colors');
                    $('#color-dropdown').html('<div class="no-results">Failed to load colors</div>');
                }
            });
        }
        
        function displayColorResults(colors, searchTerm) {
            const $dropdown = $('#color-dropdown');
            if (colors.length === 0) {
                if (searchTerm.trim()) {
                    $dropdown.html(`
                        <div class="color-dropdown-item" data-custom="true" data-name="${searchTerm}">
                            <div style="display:flex;align-items:center;">
                                <div class="color-preview" style="background-color:#ccc;width:20px;height:20px;border-radius:50%;border:1px solid #ddd;margin-right:8px;"></div>
                                <span>Create custom color: "${searchTerm}"</span>
                            </div>
                        </div>`);
                } else {
                    $dropdown.html('<div class="no-results">No colors found</div>');
                }
                return;
            }
            let html = '';
            colors.forEach(function(color){
                const stockInfo = color.has_stock ? `${color.stock_grams}g available` : 'No stock';
                const stockClass = color.has_stock ? 'text-success' : 'text-warning';
                const colorPreview = color.hex_code || '#ccc';
                const descLine = color.description ? `<div style=\"font-size:11px;color:#6c757d;\">${escapeHtml(color.description)}</div>` : '';
                html += `
                    <div class="color-dropdown-item" data-id="${color.id}" data-name="${escapeHtml(color.name)}" data-description="${escapeHtml(color.description || '')}" data-stock="${color.stock_grams}" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;">
                        <div style="display:flex;align-items:flex-start;">
                            <div class="color-preview" style="background-color:${colorPreview};width:20px;height:20px;border-radius:50%;border:1px solid #ddd;margin-right:8px;"></div>
                            <div style="display:flex;flex-direction:column;">
                                <span>${escapeHtml(color.name)}</span>
                                ${descLine}
                            </div>
                        </div>
                        <span class="color-stock-info ${stockClass}" style="font-size:0.8em;color:#666;">${stockInfo}</span>
                    </div>`;
            });
            if (searchTerm.trim() && !colors.some(c => c.name.toLowerCase() === searchTerm.toLowerCase())) {
                html += `
                    <div class="color-dropdown-item" data-custom="true" data-name="${searchTerm}" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;">
                        <div class="color-preview" style="background-color:#ccc;width:20px;height:20px;border-radius:50%;border:1px solid #ddd;margin-right:8px;"></div>
                        <span>Create custom color: "${searchTerm}"</span>
                    </div>`;
            }
            $dropdown.html(html);
            $('.color-dropdown-item').on('click', function(){
                const $item = $(this);
                const colorName = $item.data('name');
                const colorId = $item.data('id') || null;
                const isCustom = $item.data('custom') || false;
                const desc = $item.data('description');
                selectedColorId = colorId;
                selectedColorName = colorName;
                $('#color-search').val(desc ? `${colorName} (${desc})` : colorName);
                $('#color-dropdown').hide();
                if (isCustom) { selectedColorId = null; }
            });
        }
        function escapeHtml(str){
            if(!str) return '';
            return str.replace(/[&<>"']/g, function(m){
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]);
            });
        }
        // Color variant management
        $('#add-color-btn').click(function() {
            const colorName = selectedColorName || $('#color-search').val().trim();
            const quantity = parseInt($('#new-quantity').val()) || 0;
            const colorUsageGrams = parseFloat($('#color-usage-grams').val()) || 0;
            
            if (!colorName) {
                alert('Please search and select a color or enter a custom color name');
                return;
            }
            
            // Check for duplicate colors
            let isDuplicate = false;
            $('.color-variant-item').each(function() {
                const existingColor = $(this).find('input[name*="[color]"]').val().toLowerCase();
                if (existingColor === colorName.toLowerCase()) {
                    isDuplicate = true;
                    return false;
                }
            });
            
            if (isDuplicate) {
                alert('This color already exists. Please choose a different color.');
                return;
            }
            
            addColorVariant(colorName, quantity, selectedColorId, colorUsageGrams);
            
            // Clear the form
            $('#color-search').val('');
            $('#color-dropdown').hide();
            $('#new-quantity').val('');
            $('#color-usage-grams').val('');
            selectedColorId = null;
            selectedColorName = '';
        });

        // Allow Enter key to add color
        $('#color-search, #new-quantity, #color-usage-grams').keypress(function(e) {
            if (e.which === 13) {
                $('#add-color-btn').click();
            }
        });
        
        function addColorVariant(color, quantity, colorId = null, colorUsageGrams = 0) {
            if (!color) {
                alert('Please enter a color name');
                return false;
            }

            const colorStyle = getColorStyle(color);
            const colorIdInput = colorId ? `<input type="hidden" name="color_variants[${colorVariantIndex}][color_id]" value="${colorId}">` : '';
            const usageDisplay = colorUsageGrams > 0 ? `<small class="text-muted">${colorUsageGrams}g per unit</small>` : '';
            
            const html = `
                <div class="color-variant-item mb-3 p-3" style="border: 1px solid #e9ecef; border-radius: 4px; background-color: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="color-badge" style="${colorStyle} display: inline-block; padding: 4px 8px; border-radius: 4px; color: white; font-size: 0.8em; margin-right: 10px;">${color}</div>
                            <input type="hidden" name="color_variants[${colorVariantIndex}][color]" value="${color}">
                            ${colorIdInput}
                            <input type="hidden" name="color_variants[${colorVariantIndex}][color_usage_grams]" value="${colorUsageGrams}">
                            <div><strong>Color:</strong> ${color}</div>
                            ${usageDisplay}
                        </div>
                        <div class="col-md-3">
                            <label>Quantity:</label>
                            <input type="number" name="color_variants[${colorVariantIndex}][quantity]" 
                                   class="form-control quantity-input" value="${quantity}" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label>Color Usage:</label>
                            <input type="number" name="color_variants[${colorVariantIndex}][color_usage_grams]" 
                                   class="form-control" value="${colorUsageGrams}" min="0" step="0.01">
                            <small class="text-muted">grams per unit</small>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="remove-color-btn btn btn-danger btn-sm" onclick="removeColorVariant(this)" title="Remove Color">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#color-variants-container').append(html);
            colorVariantIndex++;
        }
        
        window.removeColorVariant = function(button) {
            $(button).closest('.color-variant-item').remove();
        };
        
        function getColorStyle(colorName) {
            const colors = {
                'black': 'background-color: #343a40; color: white;',
                'white': 'background-color: #f8f9fa; color: black; border: 1px solid #dee2e6;',
                'red': 'background-color: #dc3545; color: white;',
                'blue': 'background-color: #007bff; color: white;',
                'green': 'background-color: #28a745; color: white;',
                'yellow': 'background-color: #ffc107; color: black;',
                'silver': 'background-color: #6c757d; color: white;',
                'golden': 'background-color: #ffd700; color: black;',
                'gold': 'background-color: #ffd700; color: black;',
                'clear': 'background-color: #e9ecef; color: black;',
                'orange': 'background-color: #fd7e14; color: white;',
                'purple': 'background-color: #6f42c1; color: white;',
                'pink': 'background-color: #e83e8c; color: white;',
                'brown': 'background-color: #795548; color: white;',
                'gray': 'background-color: #6c757d; color: white;',
                'grey': 'background-color: #6c757d; color: white;',
                'no color': 'background-color: #e9ecef; color: black; border: 1px solid #dee2e6;'
            };
            return colors[colorName.toLowerCase()] || 'background-color: #6c757d; color: white;';
        }

        // Load existing color variants
        @if($product->colorVariants->count() > 0)
            @foreach($product->colorVariants as $variant)
                addColorVariant('{{ $variant->color }}', {{ $variant->quantity }}, {{ $variant->color_id ?? 'null' }}, {{ $variant->color_usage_grams ?? 0 }});
            @endforeach
        @endif
        
    });
</script>
@endpush
