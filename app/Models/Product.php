<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'subcategory_id',
        'color',
        'quantity',
        'price',
        'hsn_code',
        'gst_rate',
        'is_composite',
        'image'
    ];

    protected $casts = [
        'is_composite' => 'boolean',
        'price' => 'decimal:2',
        'gst_rate' => 'decimal:2'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function colorVariants(): HasMany
    {
        return $this->hasMany(ProductColorVariant::class);
    }

    // For composite products - components that make up this product
    public function components(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'parent_product_id');
    }

    // For simple products - composite products that use this as a component
    public function usedInComposites(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'component_product_id');
    }

    // Get all component products for a composite product
    public function componentProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_components', 'parent_product_id', 'component_product_id')
                    ->withPivot('quantity_needed')
                    ->withTimestamps();
    }

    // Check if product has sufficient stock
    public function hasSufficientStock(int $requiredQuantity): bool
    {
        return $this->quantity >= $requiredQuantity;
    }

    // Check if composite product can be assembled
    public function canAssemble(int $quantity = 1): bool
    {
        if (!$this->is_composite) {
            return $this->hasSufficientStock($quantity);
        }

        foreach ($this->components as $component) {
            $requiredQuantity = $component->quantity_needed * $quantity;
            if (!$component->componentProduct->hasSufficientStock($requiredQuantity)) {
                return false;
            }
        }

        return true;
    }

    // Scope to find products by name regardless of color
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    // Scope to find color variants of a product
    public function scopeColorVariants($query, $name)
    {
        return $query->where('name', $name)->orderBy('color');
    }

    // Get display name with color
    public function getDisplayNameAttribute(): string
    {
        return $this->color ? "{$this->name} ({$this->color})" : $this->name;
    }

    // Get total stock across all color variants
    public function getTotalColorVariantStock(): int
    {
        return $this->colorVariants()->sum('quantity');
    }

    // Check if product has color variants
    public function hasColorVariants(): bool
    {
        return $this->colorVariants()->exists();
    }

    // Get color variant by color name
    public function getColorVariant(string $color): ?ProductColorVariant
    {
        return $this->colorVariants()->where('color', $color)->first();
    }

    // Create or update color variant
    public function updateColorVariant(string $color, int $quantity): ProductColorVariant
    {
        return $this->colorVariants()->updateOrCreate(
            ['color' => $color],
            ['quantity' => $quantity]
        );
    }

    // Get total stock across all variants (legacy support)
    public function getTotalStockAcrossVariants(): int
    {
        return $this->hasColorVariants() ? $this->getTotalColorVariantStock() : $this->quantity;
    }
}
