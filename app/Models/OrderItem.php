<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'color_variant_id',
        'quantity',
        'price',
        'subtotal'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colorVariant(): BelongsTo
    {
        return $this->belongsTo(ProductColorVariant::class, 'color_variant_id');
    }

    public function getProductDisplayNameAttribute(): string
    {
        if ($this->colorVariant) {
            return "{$this->product->name} ({$this->colorVariant->color})";
        }
        return $this->product->name;
    }

    public function getAvailableStockAttribute(): int
    {
        if ($this->colorVariant) {
            return $this->colorVariant->quantity;
        }
        return $this->product->quantity;
    }

    public function hasSufficientStock(): bool
    {
        return $this->available_stock >= $this->quantity;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->hasSufficientStock()) {
            return 'sufficient';
        }
        
        $shortage = $this->quantity - $this->available_stock;
        if ($shortage <= 5) {
            return 'low';
        }
        
        return 'insufficient';
    }

    public function getStockStatusClassAttribute(): string
    {
        return match($this->stock_status) {
            'sufficient' => 'text-success',
            'low' => 'text-warning',
            'insufficient' => 'text-danger',
            default => 'text-secondary'
        };
    }

    public function getShortageQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->available_stock);
    }
}