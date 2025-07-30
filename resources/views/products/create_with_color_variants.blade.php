@extends('layouts.admin')

@section('title', 'Create Product')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@push('styles')
<style>
.loader-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(255, 255, 255, 0.9) !important;
    z-index: 9999999 !important;
    display: none;
    justify-content: center !important;
    align-items: center !important;
    backdrop-filter: blur(5px) !important;
    pointer-events: all !important;
    cursor: wait !important;
    margin: 0 !important;
    padding: 0 !important;
    touch-action: none !important;
    user-select: none !important;
    -webkit-user-select: none !important;
}
#loader-text {
    font-size: 14px;
    color: #666;
    margin-top: 8px;
}
.color-variant-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    background-color: #f8f9fa;
}
.color-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 0.8em;
    margin-right: 10px;
    min-width: 80px;
    text-align: center;
}
.quantity-input {
    width: 100px;
    text-align: center;
}
.remove-color-btn {
    border: none;
    background: none;
    color: #dc3545;
    font-size: 1.2em;
    cursor: pointer;
    margin-left: 10px;
}
.add-color-section {
    border: 2px dashed #dee2e6;
    padding: 20px;
    text-align: center;
    border-radius: 4px;
    margin-top: 10px;
}
</style>
@endpush

@section('content')
<!-- Loader Overlay -->
<div class="loader-overlay d-none" style="display: none !important;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <div id="loader-text">Processing...</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create New Product</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST" id="product-form" novalidate onsubmit="return false;">
            @csrf
            
            <!-- Basic Product Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        <small class="form-text text-muted">Enter the main product name (e.g., "Car Tire")</small>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price">Price (₹) *</label>
                        <input type="number" name="price" id="price" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                        @error('price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="subcategory_id">Subcategory *</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-control @error('subcategory_id') is-invalid @enderror" required>
                            <option value="">Select Category First</option>
                        </select>
                        @error('subcategory_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Color Variants Section -->
            <div class="form-group">
                <label><strong>Product Quantity & Colors</strong></label>
                <small class="form-text text-muted">
                    <strong>Option 1:</strong> Enter quantity below for a product without specific colors<br>
                    <strong>Option 2:</strong> Add specific colors with their quantities (like Red: 50, Blue: 100)
                </small>
                
                <!-- Default Quantity (No Color) -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Default Quantity (No Specific Color)</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="number" id="default-quantity" class="form-control" placeholder="Enter quantity for product without specific color" min="0" value="0">
                                <small class="form-text text-muted">Leave as 0 if you want to add specific colors below</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="color-variants-container">
                    <!-- Color variants will be added here -->
                </div>
                
                <div class="add-color-section">
                    <h6>Add Specific Colors (Optional)</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" id="new-color" class="form-control" placeholder="Enter color (e.g., Red, Blue)" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <input type="number" id="new-quantity" class="form-control" placeholder="Enter quantity" min="0">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-success" id="add-color-btn">
                                <i class="fas fa-plus"></i> Add Color
                            </button>
                        </div>
                    </div>
                </div>
                
                @error('color_variants') <span class="text-danger">{{ $message }}</span> @enderror
                @error('color_variants.*') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Composite Product Section -->
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="is_composite" value="0">
                    <input type="checkbox" name="is_composite" class="custom-control-input" id="is_composite" value="1" {{ old('is_composite') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_composite">Is Composite Product?</label>
                </div>
            </div>

            <div id="components-wrapper" class="{{ old('is_composite') ? '' : 'd-none' }}">
                <h4>Components</h4>
                <div id="components-container">
                    <!-- Component rows will be added here -->
                </div>
                <button type="button" id="add-component-btn" class="btn btn-sm btn-success">Add Component</button>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let colorVariantIndex = 0;
    
    // Subcategory fetcher
    var oldCategoryId = "{{ old('category_id') }}";
    if(oldCategoryId) {
        fetchSubcategories(oldCategoryId, "{{ old('subcategory_id') }}");
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

    // Color variant management
    $('#add-color-btn').click(function() {
        const color = $('#new-color').val().trim();
        const quantity = parseInt($('#new-quantity').val()) || 0;
        
        if (!color) {
            alert('Please enter a color name');
            return;
        }
        
        // Check for duplicate colors
        let isDuplicate = false;
        $('.color-variant-item').each(function() {
            const existingColor = $(this).find('input[name*="[color]"]').val().toLowerCase();
            if (existingColor === color.toLowerCase()) {
                isDuplicate = true;
                return false;
            }
        });
        
        if (isDuplicate) {
            alert('This color already exists. Please choose a different color.');
            return;
        }
        
        addColorVariant(color, quantity);
        $('#new-color').val('');
        $('#new-quantity').val('');
    });
    
    // Allow Enter key to add color
    $('#new-color, #new-quantity').keypress(function(e) {
        if (e.which === 13) {
            $('#add-color-btn').click();
        }
    });
    
    function addColorVariant(color, quantity) {
        if (!color) {
            alert('Please enter a color name');
            return false;
        }

        // Check for duplicate colors
        let isDuplicate = false;
        $('.color-variant-item').each(function() {
            if ($(this).find('input[name*="[color]"]').val().toLowerCase() === color.toLowerCase()) {
                isDuplicate = true;
                return false;
            }
        });

        if (isDuplicate) {
            alert('This color already exists');
            return false;
        }

        const colorStyle = getColorStyle(color);
        const html = `
            <div class="color-variant-item">
                <div class="color-badge" style="${colorStyle}">${color}</div>
                <div class="flex-grow-1">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="color_variants[${colorVariantIndex}][color]" value="${color}">
                            <strong>Color:</strong> ${color}
                        </div>
                        <div class="col-md-4">
                            <label>Quantity:</label>
                            <input type="number" name="color_variants[${colorVariantIndex}][quantity]" 
                                   class="form-control quantity-input" value="${quantity}" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="remove-color-btn" onclick="removeColorVariant(this)" title="Remove Color">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
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

    // Composite product fields
    $('#is_composite').change(function() {
        $('#components-wrapper').toggleClass('d-none', !this.checked);
        // Clear existing components when toggling
        if (!this.checked) {
            $('#components-container').empty();
        }
    });

    // Update component products when category changes
    $('#category_id').change(function() {
        // Clear existing components when category changes
        $('#components-container').empty();
    });

    $('#add-component-btn').click(function() {
        var selectedCategoryId = $('#category_id').val();
        
        if (!selectedCategoryId) {
            alert('Please select a category first before adding components.');
            return;
        }

        var componentIndex = $('#components-container .component-row').length;
        
        $('#components-container').append(`
            <div class="row component-row mb-2">
                <div class="col-md-6">
                    <select name="components[${componentIndex}][component_product_id]" class="form-control component-select" required>
                        <option value="">Loading components...</option>
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
        
        // Load components for the selected category
        loadComponentsForCategory(selectedCategoryId, componentIndex);
    });

    function loadComponentsForCategory(categoryId, componentIndex) {
        if (!categoryId) return;

        showLoader();
        $.ajax({
            url: `/api/products/by-category-components`,
            type: 'GET',
            data: { category_id: categoryId },
            success: function(data) {
                console.log('Component data received:', data); // Debug log
                var productOptions = '<option value="">Select Component Product</option>';
                
                if (data && data.length > 0) {
                    // Filter out the current product if it exists
                    const currentProductId = $('#product-form').data('product-id');
                    const filteredData = currentProductId ? 
                        data.filter(p => p.id !== currentProductId) : data;

                    productOptions += filteredData.map(p => {
                        // Debug log for each product
                        console.log('Processing product:', p);
                        
                        // Calculate total available quantity
                        let availableQty = 0;
                        if (p.color_variants && p.color_variants.length > 0) {
                            console.log('Color variants found:', p.color_variants);
                            // Sum up quantities from color variants
                            availableQty = p.color_variants.reduce((sum, variant) => {
                                const qty = parseInt(variant.quantity) || 0;
                                console.log(`Color: ${variant.color}, Qty: ${qty}`);
                                return sum + qty;
                            }, 0);
                        } else if (p.stock_logs && p.stock_logs.length > 0) {
                            // Try to get quantity from stock logs
                            console.log('Stock logs found:', p.stock_logs);
                            availableQty = p.stock_logs.reduce((sum, log) => {
                                return sum + (parseInt(log.quantity) || 0);
                            }, 0);
                        } else if (p.quantity !== undefined && p.quantity !== null) {
                            // Use direct quantity if available
                            availableQty = parseInt(p.quantity) || 0;
                            console.log('Direct quantity found:', availableQty);
                        }
                        
                        console.log(`Final available quantity for ${p.name}:`, availableQty);
                        return `<option value="${p.id}">${p.name} (Available: ${availableQty})</option>`;
                    }).join('');
                } else {
                    productOptions += '<option value="">No simple products available in this category</option>';
                }
                
                $(`.component-select:eq(${componentIndex})`).html(productOptions);
            },
            error: function(xhr) {
                console.error('Error loading components:', xhr);
                $(`.component-select:eq(${componentIndex})`).html('<option value="">Error loading products</option>');
            },
            complete: function() {
                hideLoader();
            }
        });
    }

    $(document).on('click', '.remove-component-btn', function() {
        $(this).closest('.component-row').remove();
    });
    
    // Show/Hide loader functions
    function preventFormSubmission() {
        $('form button[type="submit"]').attr('type', 'button');
        $('form').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    }

    function showLoader() {
        if (!isSubmitting) {
            isSubmitting = true;
            const $overlay = $('.loader-overlay');
            $overlay.css('display', 'flex').removeClass('d-none');
            $('body').css('cursor', 'wait').addClass('overflow-hidden');
            preventFormSubmission();
        }
    }

    function hideLoader() {
        const $overlay = $('.loader-overlay');
        $overlay.css('display', 'none').addClass('d-none');
        $('body').css('cursor', 'default').removeClass('overflow-hidden');
        isSubmitting = false;
    }

    let isSubmitting = false;
    
    // Initialize form submission prevention
    preventFormSubmission();

    // Form validation and submission
    $('#product-form button.btn-primary').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Prevent double submission
        if (isSubmitting) {
            return false;
        }

        // Get form elements
        const $form = $('#product-form');
        const $submitBtn = $(this);
        const colorVariants = $('.color-variant-item').length;
        const defaultQuantity = parseInt($('#default-quantity').val()) || 0;
        const name = $('#name').val().trim();
        const price = $('#price').val();
        const categoryId = $('#category_id').val();
        const subcategoryId = $('#subcategory_id').val();

        // Validation checks first, before showing loader
        if (colorVariants === 0 && defaultQuantity === 0) {
            alert('Please either enter a default quantity or add specific color variants.');
            return false;
        }

        if (!name || !price || !categoryId || !subcategoryId) {
            alert('Please fill in all required fields.');
            return false;
        }

        // Show loader only after validation passes
        showLoader();
        
        // If using default quantity, add "No Color" variant
        if (colorVariants === 0 && defaultQuantity > 0) {
            addColorVariant('No Color', defaultQuantity);
        }

        // All validations passed, prepare for submission
        isSubmitting = true;
        $submitBtn.prop('disabled', true).text('Creating Product...');

        // Submit the form using AJAX
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                if (response.success || response.redirect) {
                    window.location.href = '{{ route('products.index') }}';
                } else {
                    hideLoader();
                    alert('Product created successfully but redirect failed. Please go back to the products list.');
                }
            },
            error: function(xhr) {
                hideLoader();
                isSubmitting = false;
                $submitBtn.prop('disabled', false).text('Create Product');
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Please correct the following errors:\n';
                    Object.keys(errors).forEach(key => {
                        errorMessage += `\n- ${errors[key][0]}`;
                    });
                    alert(errorMessage);
                } else {
                    // Other errors
                    console.error('Server Error:', xhr);
                    alert('An error occurred while saving the product. Please try again.');
                }
            }
        });
    });

    // Reset form state when validation fails or page loads
    function resetFormState() {
        const $form = $('#product-form');
        const $submitBtn = $form.find('button[type="submit"]');
        
        // Reset form states without calling hideLoader
        $form.data('processing', false);
        $submitBtn.prop('disabled', false);
        
        // Ensure the overlay is hidden
        $('.loader-overlay').css('display', 'none').addClass('d-none');
        
        // Re-enable form interactions
        $form.find('input, select, button').prop('disabled', false);
        
        // Reset body state
        $('body').css('cursor', 'default').removeClass('overflow-hidden');
        
        // Reset submission state
        isSubmitting = false;
    }

    // Ensure loader is hidden on page load
    $('.loader-overlay').addClass('d-none').hide();
    
    // Call reset on page load
    resetFormState();
    
    // Call reset when validation errors exist
    if (document.querySelector('.is-invalid')) {
        resetFormState();
    }
    
    // Handle any stray loader states
    $(document).on('mousemove keydown', function() {
        if ($('#product-form').find(':input:disabled').length && !$('#product-form').data('processing')) {
            resetFormState();
        }
    });
    
    // Load old color variants if validation failed
    @if(old('color_variants'))
        @foreach(old('color_variants') as $index => $variant)
            @if(!empty($variant['color']))
                addColorVariant('{{ $variant['color'] }}', {{ $variant['quantity'] ?? 0 }});
            @endif
        @endforeach
    @endif

    // Global error handler
    window.addEventListener('error', function() {
        resetFormState();
    });

    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            resetFormState();
        }
    });
});
</script>
@endpush

