<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'color_variant_id',
        'quantity',
        'price',
        'gst_rate',
        'cgst',
        'sgst',
        'subtotal'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colorVariant(): BelongsTo
    {
        return $this->belongsTo(ProductColorVariant::class, 'color_variant_id');
    }

    public function getTotalTaxAttribute(): float
    {
        return $this->cgst + $this->sgst;
    }

    public function getLineAmountAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
