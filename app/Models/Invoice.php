<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'cgst',
        'sgst',
        'grand_total',
        'status',
        'paid_amount',
        'paid_date',
        'payment_method',
        'notes',
        'invoice_type' // Added invoice_type field
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_type' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getTotalTaxAttribute(): float
    {
        return $this->cgst + $this->sgst;
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getAmountDueAttribute(): float
    {
        return $this->grand_total - $this->paid_amount;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status !== 'paid' && 
               $this->status !== 'cancelled';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->paid_amount >= $this->grand_total;
    }

    public function markAsPaid($amount = null, $date = null, $method = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $amount ?? $this->grand_total,
            'paid_date' => $date ?? now(),
            'payment_method' => $method
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->isOverdue() && $this->status !== 'paid') {
            $this->update(['status' => 'overdue']);
        }
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'paid' => 'badge-success',
            'sent' => 'badge-info',
            'draft' => 'badge-secondary',
            'cancelled' => 'badge-danger',
            'overdue' => 'badge-warning',
            default => 'badge-secondary'
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::latest('id')->first();
        $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;
        return 'INV-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Helper methods to check invoice type
    public function isGst(): bool
    {
        return $this->invoice_type === 'gst';
    }

    public function isNonGst(): bool
    {
        return $this->invoice_type === 'non_gst';
    }

    // Discount calculation methods
    public function calculateDiscountAmount(float $subtotal): float
    {
        if ($this->discount_value <= 0) {
            return 0;
        }

        if ($this->discount_type == 1) {
            // Percentage discount
            return ($subtotal * $this->discount_value) / 100;
        } else {
            // Fixed amount discount
            return min($this->discount_value, $subtotal); // Don't allow discount greater than subtotal
        }
    }

    public function getDiscountTypeTextAttribute(): string
    {
        return $this->discount_type == 1 ? 'Percentage' : 'Fixed Amount';
    }

    public function getDiscountDisplayAttribute(): string
    {
        if ($this->discount_value <= 0) {
            return 'No Discount';
        }

        if ($this->discount_type == 1) {
            return $this->discount_value . '%';
        } else {
            return '₹' . number_format($this->discount_value, 2);
        }
    }

    public function getSubtotalAfterDiscountAttribute(): float
    {
        return $this->total_amount - $this->discount_amount;
    }
}
