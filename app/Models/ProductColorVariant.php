<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColorVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color',
        'quantity',
        'color_id',
        'color_usage_grams'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'color_usage_grams' => 'decimal:2'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colorModel(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    // Check if variant has sufficient stock
    public function hasSufficientStock(int $requiredQuantity): bool
    {
        return $this->quantity >= $requiredQuantity;
    }

    // Check if color has sufficient stock for production
    public function hasColorSufficientStock(int $productQuantity): bool
    {
        if (!$this->colorModel || $this->color_usage_grams <= 0) {
            return true; // No color restriction
        }
        
        $requiredColorGrams = $this->color_usage_grams * $productQuantity;
        return $this->colorModel->hasSufficientStock($requiredColorGrams);
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

    // Get total color usage for given quantity
    public function getTotalColorUsage(int $quantity): float
    {
        return $this->color_usage_grams * $quantity;
    }

    // Get color usage display
    public function getColorUsageDisplayAttribute(): string
    {
        if ($this->color_usage_grams > 0) {
            return "{$this->color_usage_grams}g per unit";
        }
        return 'No color usage specified';
    }
}
