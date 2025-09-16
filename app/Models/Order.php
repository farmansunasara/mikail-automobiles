<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'delivery_date',
        'total_amount',
        'status',
        'notes',
        'invoice_id'
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function isOverdue(): bool
    {
        return $this->delivery_date && 
               $this->delivery_date->isPast() && 
               $this->status === 'PENDING';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canCreateInvoice(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'badge-success',
            'pending' => $this->isOverdue() ? 'badge-warning' : 'badge-info',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    public static function generateOrderNumber(): string
    {
        $lastOrder = self::latest('id')->first();
        $nextNumber = $lastOrder ? $lastOrder->id + 1 : 1;
        return 'ORD-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Simplified scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('delivery_date', '<', now())
                    ->where('status', 'pending');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }
}