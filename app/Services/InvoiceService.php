<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\ProductColorVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    /**
     * Create GST invoice with optimized performance
     */
    public function createGstInvoice(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Bulk fetch all color variants to avoid N+1 queries
            $variantIds = $this->extractVariantIds($data['items']);
            $colorVariants = ProductColorVariant::with('product')
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');

            // 2. Validate stock and prepare items in one pass
            $invoiceItems = $this->prepareAndValidateItems($data['items'], $colorVariants);

            // 3. Calculate totals efficiently
            $totals = $this->calculateTotals($invoiceItems, $data);

            // 4. Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'total_amount' => $totals['total_amount'],
                'discount_type' => $data['discount_type'] ?? 0,
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'packaging_fees' => $data['packaging_fees'] ?? 0,
                'gst_rate' => $data['gst_rate'],
                'cgst' => $totals['cgst'],
                'sgst' => $totals['sgst'],
                'grand_total' => $totals['grand_total'],
                'invoice_type' => 'gst',
            ]);

            // 5. Bulk create invoice items
            $this->createInvoiceItems($invoice, $invoiceItems, $data['gst_rate']);

            // 6. Bulk update stock
            $this->updateStock($invoiceItems);

            // 7. Update order status if invoice was created from an order
            if (isset($data['order_id']) && $data['order_id']) {
                $order = Order::find($data['order_id']);
                if ($order && $order->status === 'pending') {
                    $order->update(['status' => 'completed']);
                    
                    Log::info('Order status updated to COMPLETED via GST invoice creation', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number
                    ]);
                }
            }

            return $invoice;
        });
    }

    /**
     * Create Non-GST invoice with optimized performance
     */
    public function createNonGstInvoice(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Similar optimization for non-GST invoices
            $variantIds = $this->extractVariantIds($data['items']);
            $colorVariants = ProductColorVariant::with('product')
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');

            $invoiceItems = $this->prepareAndValidateItems($data['items'], $colorVariants);
            $totals = $this->calculateNonGstTotals($invoiceItems, $data);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'total_amount' => $totals['total_amount'],
                'discount_type' => $data['discount_type'] ?? 0,
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'packaging_fees' => $data['packaging_fees'] ?? 0,
                'gst_rate' => 0, // ✅ FIXED: Non-GST invoices have 0% GST rate
                'cgst' => 0,
                'sgst' => 0,
                'grand_total' => $totals['grand_total'],
                'invoice_type' => 'non_gst',
            ]);

            $this->createInvoiceItems($invoice, $invoiceItems, 0);
            $this->updateStock($invoiceItems);

            // Update order status if invoice was created from an order
            if (isset($data['order_id']) && $data['order_id']) {
                $order = Order::find($data['order_id']);
                if ($order && $order->status === 'pending') {
                    $order->update(['status' => 'completed']);
                    
                    Log::info('Order status updated to COMPLETED via Non-GST invoice creation', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number
                    ]);
                }
            }

            return $invoice;
        });
    }

    /**
     * Extract all variant IDs from items array
     */
    private function extractVariantIds(array $items): array
    {
        $variantIds = [];
        foreach ($items as $item) {
            if (isset($item['variants'])) {
                foreach ($item['variants'] as $variant) {
                    if (isset($variant['product_id']) && intval($variant['quantity'] ?? 0) > 0) {
                        $variantIds[] = $variant['product_id'];
                    }
                }
            }
        }
        return array_unique($variantIds);
    }

    /**
     * Prepare and validate items with stock check
     */
    private function prepareAndValidateItems(array $items, $colorVariants): array
    {
        $invoiceItems = [];
        
        foreach ($items as $item) {
            $price = floatval($item['price']);
            if (isset($item['variants'])) {
                foreach ($item['variants'] as $variant) {
                    $quantity = intval($variant['quantity'] ?? 0);
                    $variantId = $variant['product_id'] ?? null;
                    
                    if ($quantity > 0 && $variantId) {
                        if (!isset($colorVariants[$variantId])) {
                            throw new \Exception("Product color variant not found (ID: {$variantId})");
                        }
                        
                        $colorVariant = $colorVariants[$variantId];
                        if ($colorVariant->quantity < $quantity) {
                            throw new \Exception("Insufficient stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
                        }
                        
                        $invoiceItems[] = [
                            'color_variant_id' => $variantId,
                            'product_id' => $colorVariant->product_id,
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $quantity * $price
                        ];
                    }
                }
            }
        }

        if (empty($invoiceItems)) {
            throw new \Exception("Please add at least one item with quantity greater than 0");
        }

        return $invoiceItems;
    }

    /**
     * Calculate totals for GST invoice
     */
    private function calculateTotals(array $invoiceItems, array $data): array
    {
        // Calculate total amount
        $totalAmount = array_sum(array_column($invoiceItems, 'subtotal'));

        // Calculate discount
        $discountType = $data['discount_type'] ?? 0;
        $discountValue = $data['discount_value'] ?? 0;
        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType == 1) {
                $discountAmount = ($totalAmount * $discountValue) / 100;
            } else {
                $discountAmount = min($discountValue, $totalAmount);
            }
        }

        $afterDiscount = $totalAmount - $discountAmount;
        
        // Add packaging fees
        $packagingFees = floatval($data['packaging_fees'] ?? 0);
        $afterPackaging = $afterDiscount + $packagingFees;

        // Calculate GST on (subtotal - discount + packaging)
        $gstRate = floatval($data['gst_rate']);
        $totalGstAmount = ($afterPackaging * $gstRate) / 100;
        $cgst = $totalGstAmount / 2;
        $sgst = $totalGstAmount / 2;

        $grandTotal = $afterPackaging + $cgst + $sgst;

        return [
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'grand_total' => $grandTotal
        ];
    }

    /**
     * Calculate totals for Non-GST invoice
     */
    private function calculateNonGstTotals(array $invoiceItems, array $data): array
    {
        $totalAmount = array_sum(array_column($invoiceItems, 'subtotal'));

        $discountType = $data['discount_type'] ?? 0;
        $discountValue = $data['discount_value'] ?? 0;
        $discountAmount = 0;

        if ($discountValue > 0) {
            if ($discountType == 1) {
                $discountAmount = ($totalAmount * $discountValue) / 100;
            } else {
                $discountAmount = min($discountValue, $totalAmount);
            }
        }

        $packagingFees = floatval($data['packaging_fees'] ?? 0);
        $grandTotal = $totalAmount - $discountAmount + $packagingFees;

        return [
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal
        ];
    }

    /**
     * Bulk create invoice items
     */
    private function createInvoiceItems(Invoice $invoice, array $invoiceItems, float $gstRate): void
    {
        $itemsToInsert = [];
        $now = now();

        foreach ($invoiceItems as $item) {
            $itemsToInsert[] = [
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'],
                'color_variant_id' => $item['color_variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'gst_rate' => $gstRate,
                'cgst' => 0, // GST calculated at invoice level
                'sgst' => 0,
                'subtotal' => $item['subtotal'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert for better performance
        InvoiceItem::insert($itemsToInsert);
    }

    /**
     * Bulk update stock quantities with atomic operations
     * Handles both simple and composite products correctly
     */
    public function updateStock(array $invoiceItems): void
    {
        // Use database-level locking to prevent race conditions
        DB::transaction(function () use ($invoiceItems) {
            foreach ($invoiceItems as $item) {
                // Lock the color variant row for update to prevent race conditions
                $colorVariant = ProductColorVariant::lockForUpdate()->find($item['color_variant_id']);
                
                if (!$colorVariant) {
                    throw new \Exception("Color variant not found (ID: {$item['color_variant_id']})");
                }
                
                // Validate stock availability with lock
                if ($colorVariant->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$item['quantity']}");
                }
                
                // Handle simple vs composite products differently
                if ($colorVariant->product->is_composite) {
                    // For composite products: Only deduct the composite product stock
                    // Components were already consumed during assembly
                    $this->stockService->outwardColorVariantStockSaleOnly(
                        $colorVariant, 
                        $item['quantity'], 
                        "Sale via Invoice (Composite Product - Components already consumed during assembly)"
                    );
                } else {
                    // For simple products: Deduct stock normally
                    $this->stockService->outwardColorVariantStockSaleOnly(
                        $colorVariant, 
                        $item['quantity'], 
                        "Sale via Invoice (Simple Product)"
                    );
                }
            }
        });
    }
}
