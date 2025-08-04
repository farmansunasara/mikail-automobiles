<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColorStockLog extends Model
{
    protected $fillable = [
        'color_id',
        'change_type',
        'quantity_grams',
        'previous_stock',
        'new_stock',
        'remarks',
        'reference_type',
        'reference_id'
    ];

    protected $casts = [
        'quantity_grams' => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock' => 'decimal:2'
    ];

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    // Get the reference model (polymorphic relationship)
    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            return $this->morphTo('reference', 'reference_type', 'reference_id');
        }
        return null;
    }

    // Scope for inward movements
    public function scopeInward($query)
    {
        return $query->where('change_type', 'inward');
    }

    // Scope for outward movements
    public function scopeOutward($query)
    {
        return $query->where('change_type', 'outward');
    }

    // Get formatted quantity with unit
    public function getFormattedQuantityAttribute(): string
    {
        $sign = $this->change_type === 'inward' ? '+' : '-';
        return "{$sign}{$this->quantity_grams}g";
    }

    // Get change type badge class
    public function getChangeTypeBadgeAttribute(): string
    {
        return $this->change_type === 'inward' ? 'badge-success' : 'badge-danger';
    }
}
