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
     * Create a new order (allows stock shortages for manufacturing)
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Check stock availability but don't prevent order creation
            //    collect shortages so we can create manufacturing requirements after order is created
            $shortages = $this->checkStockAvailability($data['items']);

            // 2. Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'total_amount' => 0
            ]);

            // 3. Create order items with stock validation
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

            Log::info('Order created with stock validation', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount
            ]);

            // 4. Create manufacturing requirements for any shortages found earlier
            if (!empty($shortages)) {
                $this->createManufacturingRequirements($order, $shortages);
            }

            return $order->fresh(['items.product', 'items.colorVariant']);
        });
    }

    /**
     * Check stock availability and log shortages (allows manufacturing orders)
     */
    private function checkStockAvailability(array $items): array
    {
        $stockShortages = [];

        foreach ($items as $item) {
            foreach ($item['variants'] as $variant) {
                if (($variant['quantity'] ?? 0) > 0) {
                    $variantId = $variant['product_id'];
                    $requiredQuantity = intval($variant['quantity']);

                    // Get current stock
                    $variantModel = ProductColorVariant::find($variantId);
                    if (!$variantModel) {
                        Log::warning("Product variant with ID {$variantId} not found during order stock check.");
                        continue;
                    }

                    // ProductColorVariant stores current stock in the `quantity` column.
                    $currentStock = $variantModel->quantity ?? 0;

                    if ($requiredQuantity > $currentStock) {
                        $shortage = $requiredQuantity - $currentStock;
                        $stockShortages[] = [
                            'product_id' => $variantModel->product_id,
                            'color_variant_id' => $variantModel->id,
                            'required_quantity' => $requiredQuantity,
                            'available_quantity' => $currentStock,
                            'shortage_quantity' => $shortage,
                            'product_name' => $variantModel->product->name ?? null,
                            'variant_color' => $variantModel->color ?? null
                        ];
                    }
                }
            }
        }

        // Log stock shortages for manufacturing planning but don't prevent order creation
        if (!empty($stockShortages)) {
            Log::info('Order contains stock shortages - manufacturing may be required', [
                'shortages' => array_map(function ($s) {
                    return "{$s['product_name']} ({$s['variant_color']}): Need {$s['shortage_quantity']} more (Available: {$s['available_quantity']})";
                }, $stockShortages),
                'message' => 'Order allowed to proceed for manufacturing planning'
            ]);
        }

        return $stockShortages;
    }

    /**
     * Create ManufacturingRequirement records for given shortages and link them to the order.
     * This is intentionally permissive: it creates an MR per shortage entry. Deduplication
     * or merging logic can be added later if desired.
     *
     * @param Order $order
     * @param array $shortages
     * @return void
     */
    private function createManufacturingRequirements(Order $order, array $shortages): void
    {
        foreach ($shortages as $s) {
            try {
                ManufacturingRequirement::create([
                    'mr_number' => ManufacturingRequirement::generateMrNumber(),
                    'order_id' => $order->id,
                    'product_id' => $s['product_id'],
                    'color_variant_id' => $s['color_variant_id'],
                    'required_quantity' => $s['required_quantity'],
                    'available_quantity' => $s['available_quantity'],
                    'shortage_quantity' => $s['shortage_quantity'],
                    'earliest_delivery_date' => $order->delivery_date ?? null,
                    'status' => 'open',
                    'notes' => 'Auto-generated from order ' . $order->order_number
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create ManufacturingRequirement', [
                    'order_id' => $order->id,
                    'shortage' => $s,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Update an existing order (simplified)
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            // Collect shortages to create manufacturing requirements after update
            $shortages = $this->checkStockAvailability($data['items']);
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

            // Create manufacturing requirements for any shortages detected earlier
            if (!empty($shortages)) {
                $this->createManufacturingRequirements($order, $shortages);
            }

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

            // Update order status to completed and link invoice
            $order->update([
                'status' => 'completed',
                'invoice_id' => $invoice->id
            ]);

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
     * Cancel an order (prevents if invoice exists)
     */
    public function cancelOrder(Order $order): void
    {
        if (!$order->canCancel()) {
            throw new Exception('Cannot cancel order. Only pending orders without invoices can be cancelled.');
        }

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
     * Note: Orders are automatically completed when invoice is created
     * This method is kept for potential future use
     */

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
