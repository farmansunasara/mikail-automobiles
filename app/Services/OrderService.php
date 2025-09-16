<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ManufacturingRequirement;
use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create a new order (simplified - no stock checks, no manufacturing requirements)
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Create order (no stock validations)
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'total_amount' => 0
            ]);

            // 2. Create order items (simplified)
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                foreach ($item['variants'] as $variant) {
                    if (($variant['quantity'] ?? 0) > 0) {
                        $quantity = intval($variant['quantity']);
                        $price = floatval($item['price']);
                        $subtotal = $quantity * $price;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'color_variant_id' => $variant['product_id'],
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $subtotal
                        ]);
                        
                        $totalAmount += $subtotal;
                    }
                }
            }

            // 3. Update total amount
            $order->update(['total_amount' => $totalAmount]);

            Log::info('Order created (simplified)', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount
            ]);

            return $order->fresh(['items.product', 'items.colorVariant']);
        });
    }

    /**
     * Update an existing order (simplified)
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            Log::info('Starting order update', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'previous_total' => $order->total_amount
            ]);
            
            // Delete existing order items
            $deletedCount = $order->items()->count();
            $order->items()->delete();
            
            Log::info('Deleted existing order items', ['count' => $deletedCount]);

            // Update order details
            $order->update([
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total_amount' => 0
            ]);

            // Create new order items (with zero-quantity filtering and validation)
            $totalAmount = 0;
            $itemsCreated = 0;
            
            foreach ($data['items'] as $itemIndex => $item) {
                // Skip items that don't have required fields
                if (!isset($item['product_id']) || !isset($item['price']) || !isset($item['variants'])) {
                    Log::debug('Skipped incomplete item', [
                        'item_index' => $itemIndex,
                        'item_data' => $item
                    ]);
                    continue;
                }
                
                foreach ($item['variants'] as $variantIndex => $variant) {
                    // Skip variants that don't have required fields
                    if (!isset($variant['product_id']) || !isset($variant['quantity'])) {
                        Log::debug('Skipped incomplete variant', [
                            'item_index' => $itemIndex,
                            'variant_index' => $variantIndex,
                            'variant_data' => $variant
                        ]);
                        continue;
                    }
                    
                    $quantity = intval($variant['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        $price = floatval($item['price']);
                        $subtotal = $quantity * $price;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'color_variant_id' => $variant['product_id'],
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $subtotal
                        ]);
                        
                        $totalAmount += $subtotal;
                        $itemsCreated++;
                        
                        Log::debug('Created order item', [
                            'product_id' => $item['product_id'],
                            'color_variant_id' => $variant['product_id'],
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $subtotal
                        ]);
                    } else {
                        Log::debug('Skipped zero quantity variant', [
                            'product_id' => $item['product_id'],
                            'color_variant_id' => $variant['product_id']
                        ]);
                    }
                }
            }

            // Update total amount
            $order->update(['total_amount' => $totalAmount]);

            Log::info('Order updated successfully (simplified)', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'items_created' => $itemsCreated,
                'new_total_amount' => $totalAmount
            ]);

            return $order->fresh(['items.product', 'items.colorVariant']);
        });
    }

    /**
     * Generate invoice from order (simplified - stock deducted here)
     */
    public function generateInvoiceFromOrder(Order $order, array $invoiceData = []): Invoice
    {
        return DB::transaction(function () use ($order, $invoiceData) {
            // Check if order can be invoiced
            if ($order->status !== 'pending') {
                throw new Exception('Only pending orders can generate invoices.');
            }

            // Determine invoice type
            $invoiceType = $invoiceData['invoice_type'] ?? 'gst';
            
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'invoice_date' => $invoiceData['invoice_date'] ?? now()->toDateString(),
                'due_date' => $invoiceData['due_date'] ?? now()->addDays(30)->toDateString(),
                'total_amount' => $order->total_amount,
                'discount_type' => $invoiceData['discount_type'] ?? 0,
                'discount_value' => $invoiceData['discount_value'] ?? 0,
                'discount_amount' => 0,
                'packaging_fees' => $invoiceData['packaging_fees'] ?? 0,
                'gst_rate' => $invoiceData['gst_rate'] ?? 18,
                'cgst' => 0,
                'sgst' => 0,
                'grand_total' => 0,
                'status' => 'draft',
                'notes' => $invoiceData['notes'] ?? "Generated from Order #{$order->order_number}",
                'invoice_type' => $invoiceType
            ]);

            // Calculate totals and update invoice
            $this->calculateInvoiceTotals($invoice, $invoiceData);

            // Create invoice items and deduct stock
            foreach ($order->items as $orderItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $orderItem->product_id,
                    'color_variant_id' => $orderItem->color_variant_id,
                    'quantity' => $orderItem->quantity,
                    'price' => $orderItem->price,
                    'gst_rate' => $invoice->gst_rate,
                    'cgst' => 0,
                    'sgst' => 0,
                    'subtotal' => $orderItem->subtotal
                ]);

                // DEDUCT STOCK HERE (only when invoice is created)
                $variant = ProductColorVariant::find($orderItem->color_variant_id);
                $previousQty = $variant->quantity;
                
                // Check if we have sufficient stock, but allow negative for simplified workflow
                if ($previousQty < $orderItem->quantity) {
                    Log::warning('Insufficient stock for invoice generation', [
                        'variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'color' => $variant->color,
                        'available' => $previousQty,
                        'required' => $orderItem->quantity,
                        'order_number' => $order->order_number
                    ]);
                }
                
                $variant->decrement('quantity', $orderItem->quantity);

                // Log stock movement
                $variant->product->stockLogs()->create([
                    'change_type' => 'outward',
                    'quantity' => $orderItem->quantity,
                    'previous_quantity' => $previousQty,
                    'new_quantity' => $variant->fresh()->quantity,
                    'color_variant_id' => $variant->id,
                    'remarks' => "Sale via Invoice #{$invoice->invoice_number}",
                ]);
            }

            // Update order status
            $order->update(['status' => 'completed']);

            Log::info('Invoice generated and stock deducted', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);

            return $invoice->fresh(['items.product', 'items.colorVariant']);
        });
    }

    /**
     * Cancel an order (simplified)
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Update order status
            $order->update(['status' => 'cancelled']);

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);
        });
    }

    /**
     * Calculate invoice totals
     */
    protected function calculateInvoiceTotals(Invoice $invoice, array $invoiceData): void
    {
        $totalAmount = $invoice->total_amount;
        $packagingFees = $invoice->packaging_fees;
        $subtotal = $totalAmount + $packagingFees;

        // Calculate discount
        $discountAmount = 0;
        if ($invoice->discount_value > 0) {
            if ($invoice->discount_type == 1) {
                $discountAmount = ($subtotal * $invoice->discount_value) / 100;
            } else {
                $discountAmount = min($invoice->discount_value, $subtotal);
            }
        }

        $afterDiscount = $subtotal - $discountAmount;

        // Calculate GST (only for GST invoices)
        $cgst = 0;
        $sgst = 0;
        if ($invoice->invoice_type === 'gst') {
            $gstAmount = ($afterDiscount * $invoice->gst_rate) / 100;
            $cgst = $gstAmount / 2;
            $sgst = $gstAmount / 2;
        }

        $grandTotal = $afterDiscount + $cgst + $sgst;

        // Update invoice with calculated amounts
        $invoice->update([
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'grand_total' => $grandTotal
        ]);
    }

    /**
     * Get order statistics for dashboard
     */
    public function getOrderStatistics(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $totalOrderValue = Order::where('status', 'pending')->sum('total_amount');

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_order_value' => $totalOrderValue
        ];
    }
}
