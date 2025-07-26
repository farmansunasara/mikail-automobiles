<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColorVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Check if variant has sufficient stock
    public function hasSufficientStock(int $requiredQuantity): bool
    {
        return $this->quantity >= $requiredQuantity;
    }

    // Get color display name with styling
    public function getColorDisplayAttribute(): string
    {
        return $this->color ?: 'Default';
    }

    // Get stock status class for UI
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) return 'danger';
        if ($this->quantity <= 10) return 'warning';
        return 'success';
    }
}
