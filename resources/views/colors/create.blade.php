@extends('layouts.admin')

@section('title', 'Create Color')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('colors.index') }}">Colors</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create New Color</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('colors.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Color Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required maxlength="100">
                        <small class="form-text text-muted">Enter the color name (e.g., Red, Blue, Metallic Silver)</small>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hex_code">Hex Color Code</label>
                        <div class="input-group">
                            <input type="text" name="hex_code" id="hex_code" class="form-control @error('hex_code') is-invalid @enderror" 
                                   value="{{ old('hex_code') }}" placeholder="#FF0000" maxlength="7">
                            <div class="input-group-append">
                                <input type="color" id="color_picker" class="form-control" style="width: 50px; padding: 0;">
                            </div>
                        </div>
                        <small class="form-text text-muted">Optional: Choose or enter hex color code for visual display</small>
                        @error('hex_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="stock_grams">Initial Stock (grams) *</label>
                        <input type="number" name="stock_grams" id="stock_grams" class="form-control @error('stock_grams') is-invalid @enderror" 
                               value="{{ old('stock_grams', 0) }}" min="0" step="0.01" required>
                        <small class="form-text text-muted">Enter initial stock quantity in grams</small>
                        @error('stock_grams') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="minimum_stock">Minimum Stock Threshold (grams) *</label>
                        <input type="number" name="minimum_stock" id="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" 
                               value="{{ old('minimum_stock', 0) }}" min="0" step="0.01" required>
                        <small class="form-text text-muted">Alert when stock falls below this level</small>
                        @error('minimum_stock') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3" maxlength="1000">{{ old('description') }}</textarea>
                <small class="form-text text-muted">Optional description or notes about this color</small>
                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" 
                           {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">Active</label>
                </div>
                <small class="form-text text-muted">Only active colors will be available for product selection</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Color
                </button>
                <a href="{{ route('colors.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Color picker functionality
    $('#color_picker').on('change', function() {
        $('#hex_code').val($(this).val().toUpperCase());
    });
    
    $('#hex_code').on('input', function() {
        var hexValue = $(this).val();
        if (hexValue.match(/^#[0-9A-Fa-f]{6}$/)) {
            $('#color_picker').val(hexValue);
        }
    });
    
    // Auto-generate hex code suggestion based on color name
    $('#name').on('blur', function() {
        var colorName = $(this).val().toLowerCase();
        var hexCode = '';
        
        // Basic color name to hex mapping
        var colorMap = {
            'red': '#FF0000',
            'blue': '#0000FF',
            'green': '#008000',
            'yellow': '#FFFF00',
            'orange': '#FFA500',
            'purple': '#800080',
            'pink': '#FFC0CB',
            'brown': '#A52A2A',
            'black': '#000000',
            'white': '#FFFFFF',
            'gray': '#808080',
            'grey': '#808080',
            'silver': '#C0C0C0',
            'gold': '#FFD700',
            'maroon': '#800000',
            'navy': '#000080',
            'teal': '#008080',
            'lime': '#00FF00',
            'olive': '#808000',
            'aqua': '#00FFFF',
            'fuchsia': '#FF00FF'
        };
        
        if (colorMap[colorName] && !$('#hex_code').val()) {
            $('#hex_code').val(colorMap[colorName]);
            $('#color_picker').val(colorMap[colorName]);
        }
    });
});
</script>
@endpush
