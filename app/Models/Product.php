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
}
