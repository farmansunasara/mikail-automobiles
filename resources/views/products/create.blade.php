@extends('layouts.admin')

@section('title', 'Create Product')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create New Product</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" name="color" id="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color') }}">
                        @error('color') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Category</label>
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
                        <label for="subcategory_id">Subcategory</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-control @error('subcategory_id') is-invalid @enderror" required>
                            <option value="">Select Category First</option>
                        </select>
                        @error('subcategory_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" name="price" id="price" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                        @error('price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="quantity">Initial Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 0) }}" required>
                        @error('quantity') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="gst_rate">GST Rate (%)</label>
                        <input type="number" name="gst_rate" id="gst_rate" step="0.01" class="form-control @error('gst_rate') is-invalid @enderror" value="{{ old('gst_rate') }}" required>
                        @error('gst_rate') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="hsn_code">HSN Code</label>
                <input type="text" name="hsn_code" id="hsn_code" class="form-control @error('hsn_code') is-invalid @enderror" value="{{ old('hsn_code') }}">
                @error('hsn_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

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
            
            <button type="submit" class="btn btn-primary mt-3">Create Product</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
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

        // Composite product fields
        $('#is_composite').change(function() {
            $('#components-wrapper').toggleClass('d-none', !this.checked);
        });

        var simpleProducts = {!! json_encode($products) !!};

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
    });
</script>
@endpush
