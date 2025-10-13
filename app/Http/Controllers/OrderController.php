<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\ManufacturingRequirement;
use App\Services\OrderService;
use App\Services\StockService;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $orderService;
    protected $stockService;

    public function __construct(OrderService $orderService, StockService $stockService)
    {
        $this->orderService = $orderService;
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product', 'items.colorVariant']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->overdue();
        }

        $orders = $query->latest('order_date')->paginate(15)->appends($request->query());
        $customers = Customer::orderBy('name')->get();

        return view('orders.index', compact('orders', 'customers'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $productNames = Product::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        $orderNumber = Order::generateOrderNumber();

        return view('orders.create', compact('customers', 'productNames', 'orderNumber', 'categories'));
    }

    /**
     * Store a newly created order (simplified)
     */
    public function store(OrderStoreRequest $request)
    {
        Log::info('Order store method called', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.price' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.variants' => 'required|array|min:1',
            'items.*.variants.*.product_id' => 'required|exists:product_color_variants,id',
            'items.*.variants.*.quantity' => 'required|integer|min:0|max:9999',
        ], [
            'items.required' => 'Please add at least one item to the order',
            'items.*.product_id.required' => 'Please select a product for each item',
            'items.*.price.required' => 'Please set a price for each item',
            'items.*.price.min' => 'Price must be greater than 0',
            'items.*.variants.required' => 'Please select at least one color variant',
            'items.*.variants.*.product_id.required' => 'Invalid color variant selected',
            'items.*.variants.*.quantity.required' => 'Please enter quantity for each variant',
            'items.*.variants.*.quantity.min' => 'Quantity cannot be negative',
        ]);

        try {
            Log::info('Order creation started', [
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'items_count' => count($request->items)
            ]);

            $order = $this->orderService->createOrder($request->all());

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully! Manufacturing requirements have been logged for any stock shortages.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Order creation failed - Database error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'input' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Database error occurred while creating order. Please try again.');
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            // Note: Stock validation errors are now logged but don't prevent order creation
            // Orders can exceed stock for manufacturing planning

            return back()->withInput()->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order (simplified)
     */
    public function show(Order $order)
    {
        $order->load([
            'customer',
            'items.product.category',
            'items.colorVariant',
            'invoice'
        ]);

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order (simplified)
     */
    public function edit(Order $order)
    {
        if (!$order->canCreateInvoice()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Only pending orders can be edited.');
        }

        $customers = Customer::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $productNames = Product::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        $order->load(['customer', 'items.product.category', 'items.colorVariant']);

        // Get all color variants for each product in the order
        $productVariants = [];
        $groupedItems = $order->items->groupBy('product_id');
        foreach ($groupedItems as $productId => $items) {
            $product = $items->first()->product;
            $allVariants = ProductColorVariant::where('product_id', $productId)
                ->with(['colorModel'])
                ->get();
            
            $existingQuantities = [];
            foreach ($items as $item) {
                $existingQuantities[$item->color_variant_id] = $item->quantity;
            }
            
            $productVariants[$productId] = [
                'product' => $product,
                'variants' => $allVariants,
                'existing_quantities' => $existingQuantities,
                'price' => $items->first()->price
            ];
        }

        return view('orders.edit', compact('order', 'customers', 'productNames', 'categories', 'productVariants'));
    }

    /**
     * Update the specified order (simplified)
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        Log::info('=== ORDER UPDATE REQUEST RECEIVED ===', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'request_method' => $request->method(),
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        if (!$order->canEdit()) {
            Log::warning('Order cannot be edited', [
                'order_id' => $order->id,
                'status' => $order->status,
                'has_invoice' => $order->hasInvoice()
            ]);
            return redirect()->route('orders.show', $order)
                ->with('error', 'Only pending orders without invoices can be edited.');
        }

        Log::info('Starting validation for order update');

        // Filter out incomplete items before validation
        $filteredItems = [];
        foreach ($request->input('items', []) as $item) {
            // Only include items that have all required fields
            if (isset($item['product_id']) && isset($item['price']) && isset($item['variants']) && !empty($item['variants'])) {
                // Filter variants to only include complete ones
                $filteredVariants = [];
                foreach ($item['variants'] as $variant) {
                    if (isset($variant['product_id']) && isset($variant['quantity']) && intval($variant['quantity']) > 0) {
                        $filteredVariants[] = $variant;
                    }
                }
                
                // Only include the item if it has at least one valid variant
                if (!empty($filteredVariants)) {
                    $item['variants'] = $filteredVariants;
                    $filteredItems[] = $item;
                }
            }
        }
        
        // Replace the items in the request with filtered items
        $request->merge(['items' => $filteredItems]);
        
        Log::info('Filtered items for validation', [
            'original_count' => count($request->input('items', [])),
            'filtered_count' => count($filteredItems),
            'filtered_items' => $filteredItems
        ]);

        try {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'order_date' => 'required|date',
                'delivery_date' => 'nullable|date|after_or_equal:order_date',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1|max:50',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.price' => 'required|numeric|min:0.01|max:999999.99',
                'items.*.variants' => 'required|array|min:1',
                'items.*.variants.*.product_id' => 'required|exists:product_color_variants,id',
                'items.*.variants.*.quantity' => 'required|integer|min:0|max:9999',
            ], [
                'items.required' => 'Please add at least one item to the order',
                'items.*.product_id.required' => 'Please select a product for each item',
                'items.*.price.required' => 'Please set a price for each item',
                'items.*.price.min' => 'Price must be greater than 0',
                'items.*.variants.required' => 'Please select at least one color variant',
                'items.*.variants.*.product_id.required' => 'Invalid color variant selected',
                'items.*.variants.*.quantity.required' => 'Please enter quantity for each variant',
                'items.*.variants.*.quantity.min' => 'Quantity cannot be negative',
            ]);
            
            Log::info('Validation passed for order update');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed for order update', [
                'errors' => $e->errors(),
                'filtered_items' => $filteredItems
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            Log::info('Order update started', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);

            $updatedOrder = $this->orderService->updateOrder($order, $request->all());

            Log::info('Order updated successfully', [
                'order_id' => $updatedOrder->id,
                'order_number' => $updatedOrder->order_number
            ]);

            return redirect()->route('orders.show', $updatedOrder)
                ->with('success', 'Order updated successfully!');

        } catch (\Exception $e) {
            Log::error('Order update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Generate invoice from order (simplified)
     */
    public function generateInvoice(Request $request, Order $order)
    {
        if (!$order->canCreateInvoice()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Only pending orders can generate invoices.');
        }

        if ($order->hasInvoice()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Order already has an invoice.');
        }

        try {
            Log::info('Invoice generation from order started', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);

            $invoice = $this->orderService->generateInvoiceFromOrder($order, $request->all());

            Log::info('Invoice generated successfully', [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);

            $redirectRoute = $invoice->invoice_type === 'gst' ? 'invoices.gst.show' : 'invoices.non_gst.show';
            return redirect()->route($redirectRoute, $invoice)
                ->with('success', 'Invoice generated successfully! Stock has been deducted.');

        } catch (\Exception $e) {
            Log::error('Invoice generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'Error generating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified order (simplified)
     */
    public function cancel(Order $order)
    {
        if (!$order->canCancel()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Only pending orders without invoices can be cancelled.');
        }

        try {
            $this->orderService->cancelOrder($order);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Error cancelling order: ' . $e->getMessage());
        }
    }

    /**
     * Note: Orders are automatically completed when invoice is created
     * No separate complete method needed
     */

    /**
     * Get product variants for AJAX requests
     */
    public function getProductVariants(Request $request)
    {
        $productName = $request->get('product_name');
        
        if (!$productName) {
            return response()->json([]);
        }

        $variants = ProductColorVariant::whereHas('product', function ($query) use ($productName) {
            $query->where('name', $productName);
        })
        ->with(['colorModel'])
        ->get();

        return response()->json($variants);
    }

    /**
     * Get stock availability for a variant
     */
    public function getStockAvailability(Request $request)
    {
        $variantId = $request->get('variant_id');
        
        if (!$variantId) {
            return response()->json(['available' => 0]);
        }

        $variant = ProductColorVariant::find($variantId);
        
        if (!$variant) {
            return response()->json(['available' => 0]);
        }

        return response()->json([
            'available' => $variant->quantity,
            'has_sufficient_stock' => $variant->quantity > 0
        ]);
    }
}
