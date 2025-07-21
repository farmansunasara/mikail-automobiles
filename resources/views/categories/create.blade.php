@extends('layouts.admin')

@section('title', 'Create Category')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create New Category</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div id="subcategories-wrapper">
                <label>Subcategories</label>
                <div class="input-group mb-2">
                    <input type="text" name="subcategories[]" class="form-control" placeholder="Subcategory name...">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-success" id="add-subcategory-btn">+</button>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#add-subcategory-btn').click(function() {
            $('#subcategories-wrapper').append(`
                <div class="input-group mb-2">
                    <input type="text" name="subcategories[]" class="form-control" placeholder="Subcategory name...">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-subcategory-btn">-</button>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove-subcategory-btn', function() {
            $(this).closest('.input-group').remove();
        });
    });
</script>
@endpush
