<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManufacturingRequirement extends Model
{
    protected $fillable = [
        'mr_number',
        'order_id',
        'product_id',
        'color_variant_id',
        'required_quantity',
        'available_quantity',
        'shortage_quantity',
        'earliest_delivery_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'required_quantity' => 'integer',
        'available_quantity' => 'integer',
        'shortage_quantity' => 'integer',
        'earliest_delivery_date' => 'date'
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

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProduction(): bool
    {
        return $this->status === 'in_production';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'open' => 'badge-warning',
            'in_production' => 'badge-info',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'in_production' => 'In Production',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getPriorityAttribute(): string
    {
        if (!$this->earliest_delivery_date) {
            return 'low';
        }

        $daysUntilDelivery = now()->diffInDays($this->earliest_delivery_date, false);
        
        if ($daysUntilDelivery < 0) {
            return 'urgent'; // Overdue
        } elseif ($daysUntilDelivery <= 3) {
            return 'high';
        } elseif ($daysUntilDelivery <= 7) {
            return 'medium';
        }
        
        return 'low';
    }

    public function getPriorityClassAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'text-danger',
            'high' => 'text-warning',
            'medium' => 'text-info',
            'low' => 'text-secondary',
            default => 'text-secondary'
        };
    }

    public static function generateMrNumber(): string
    {
        $lastMr = self::latest('id')->first();
        $nextNumber = $lastMr ? $lastMr->id + 1 : 1;
        return 'MR-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Scope for filtering manufacturing requirements
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where(function ($q) use ($priority) {
            switch ($priority) {
                case 'urgent':
                    $q->where('earliest_delivery_date', '<', now());
                    break;
                case 'high':
                    $q->whereBetween('earliest_delivery_date', [now(), now()->addDays(3)]);
                    break;
                case 'medium':
                    $q->whereBetween('earliest_delivery_date', [now()->addDays(4), now()->addDays(7)]);
                    break;
                case 'low':
                    $q->where('earliest_delivery_date', '>', now()->addDays(7));
                    break;
            }
        });
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProduction($query)
    {
        return $query->where('status', 'in_production');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Aggregate shortages by product/variant
    public static function getAggregatedShortages()
    {
        return self::selectRaw('
                product_id,
                color_variant_id,
                SUM(shortage_quantity) as total_shortage,
                MIN(earliest_delivery_date) as earliest_delivery_date,
                COUNT(*) as requirement_count
            ')
            ->where('status', 'open')
            ->groupBy('product_id', 'color_variant_id')
            ->orderBy('earliest_delivery_date', 'asc')
            ->get();
    }
}