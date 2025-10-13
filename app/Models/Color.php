<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    protected $fillable = [
        'name',
        'hex_code',
        'stock_grams',
        'minimum_stock',
        'description',
        'is_active'
    ];

    protected $casts = [
        'stock_grams' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function productColorVariants(): HasMany
    {
        return $this->hasMany(ProductColorVariant::class);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(ColorStockLog::class);
    }

    // Check if color has sufficient stock
    public function hasSufficientStock(float $requiredGrams): bool
    {
        if ($this->stock_grams < 0) {
            return false; // Negative stock is invalid
        }
        return $this->stock_grams >= $requiredGrams;
    }

    // Validate stock consistency
    public function validateStock(): array
    {
        $errors = [];

        if ($this->stock_grams < 0) {
            $errors[] = 'Stock cannot be negative.';
        }

        if ($this->minimum_stock < 0) {
            $errors[] = 'Minimum stock threshold cannot be negative.';
        }

        if ($this->stock_grams < $this->minimum_stock) {
            $errors[] = 'Current stock is below minimum threshold.';
        }

        return $errors;
    }

    // Get stock status for UI
    public function getStockStatusAttribute(): string
    {
        if ($this->stock_grams <= 0) return 'danger';
        if ($this->stock_grams <= $this->minimum_stock) return 'warning';
        return 'success';
    }

    // Get display name with stock info
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->stock_grams}g available)";
    }

    // Scope for active colors
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for low stock colors
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_grams', '<=', 'minimum_stock');
    }

    // Scope for searchable colors
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
