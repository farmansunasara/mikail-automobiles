@props(['column', 'label'])

@php
    $currentSort = request('sort');
    $currentDirection = request('direction', 'asc');
    $newDirection = ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc';
    
    // Build URL with current query parameters
    $params = array_merge(request()->query(), [
        'sort' => $column,
        'direction' => $newDirection
    ]);
    $sortUrl = request()->url() . '?' . http_build_query($params);
    
    // Determine icon
    $icon = 'fas fa-sort text-muted';
    if ($currentSort === $column) {
        $icon = $currentDirection === 'asc' ? 'fas fa-sort-up text-primary' : 'fas fa-sort-down text-primary';
    }
@endphp

<th class="sortable-header">
    <a href="{{ $sortUrl }}" 
       class="text-decoration-none text-dark d-flex align-items-center justify-content-between"
       style="color: inherit !important;">
        <span>{{ $label }}</span>
        <i class="{{ $icon }} ml-1" style="font-size: 0.8em;"></i>
    </a>
</th>
