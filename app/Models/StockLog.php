<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = [
        'product_id',
        'color_variant_id',
        'change_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'remarks'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colorVariant(): BelongsTo
    {
        return $this->belongsTo(ProductColorVariant::class, 'color_variant_id');
    }

    public function scopeInward($query)
    {
        return $query->where('change_type', 'inward');
    }

    public function scopeOutward($query)
    {
        return $query->where('change_type', 'outward');
    }
}
