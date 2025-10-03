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

    public function manufacturingRequirements(): HasMany
    {
        return $this->hasMany(ManufacturingRequirement::class);
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
        if ($this->hasColorVariants()) {
            return $this->getTotalColorVariantStock() >= $requiredQuantity;
        }
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

    // Validate business rules for product operations
    public function validateBusinessRules(): array
    {
        $errors = [];

        // Check if product has sufficient stock for operations
        if ($this->getTotalStockAcrossVariants() < 0) {
            $errors[] = 'Product cannot have negative stock.';
        }

        // Check composite product component availability
        if ($this->is_composite) {
            foreach ($this->components as $component) {
                $componentStock = $component->componentProduct->getTotalStockAcrossVariants();
                if ($componentStock < $component->quantity_needed) {
                    $errors[] = "Component '{$component->componentProduct->name}' has insufficient stock. Required: {$component->quantity_needed}, Available: {$componentStock}";
                }
            }
        }

        // Check color variant stock consistency
        foreach ($this->colorVariants as $variant) {
            if ($variant->quantity < 0) {
                $errors[] = "Color variant '{$variant->color}' cannot have negative stock.";
            }
            if ($variant->minimum_threshold < 0) {
                $errors[] = "Color variant '{$variant->color}' minimum threshold cannot be negative.";
            }
        }

        return $errors;
    }

    // Check if product can be safely deleted
    public function canBeDeleted(): array
    {
        $errors = [];

        if ($this->invoiceItems()->exists()) {
            $errors[] = 'Cannot delete product with associated invoices.';
        }

        if ($this->orderItems()->exists()) {
            $errors[] = 'Cannot delete product with associated orders.';
        }

        // Check if this product is used as a component in other products
        $usedInComposites = $this->usedInComposites()->with('parentProduct')->get();
        if ($usedInComposites->count() > 0) {
            $productNames = $usedInComposites->pluck('parentProduct.name')->implode(', ');
            $errors[] = "Cannot delete product used as component in: {$productNames}";
        }

        return $errors;
    }

    // Validate component relationships
    public function validateComponentRelationships(): array
    {
        $errors = [];

        if (!$this->is_composite) {
            return $errors; // No components to validate
        }

        foreach ($this->components as $component) {
            // Check if component product exists and is not composite
            if (!$component->componentProduct) {
                $errors[] = "Component product not found for component ID: {$component->id}";
                continue;
            }

            if ($component->componentProduct->is_composite) {
                $errors[] = "Cannot use composite product '{$component->componentProduct->name}' as a component.";
            }

            // Check for self-reference
            if ($component->component_product_id == $this->id) {
                $errors[] = "Product cannot be a component of itself.";
            }

            // Check quantity requirements
            if ($component->quantity_needed <= 0) {
                $errors[] = "Component '{$component->componentProduct->name}' quantity must be greater than 0.";
            }
        }

        return $errors;
    }
}
