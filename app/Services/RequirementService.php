<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductColorVariant;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequirementService
{
    /**
     * Calculate manufacturing requirements dynamically
     */
    public function calculateDynamicRequirements(): array
    {
        $requirements = [];
        
        // 1. Get all pending orders
        $pendingOrders = Order::where('status', 'pending')
            ->with(['customer', 'items.product', 'items.colorVariant'])
            ->get();
        
        // 2. Calculate total demand per variant
        $demand = [];
        foreach ($pendingOrders as $order) {
            foreach ($order->items as $item) {
                $variantId = $item->color_variant_id;
                if (!isset($demand[$variantId])) {
                    $demand[$variantId] = [
                        'variant' => $item->colorVariant,
                        'product' => $item->product,
                        'total_demand' => 0,
                        'orders' => []
                    ];
                }
                $demand[$variantId]['total_demand'] += $item->quantity;
                $demand[$variantId]['orders'][] = [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name,
                    'quantity' => $item->quantity,
                    'delivery_date' => $order->delivery_date
                ];
            }
        }
        
        // 3. Calculate shortages with component breakdown
        foreach ($demand as $variantId => $data) {
            $currentStock = $data['variant']->quantity;
            $shortage = max(0, $data['total_demand'] - $currentStock);
            
            if ($shortage > 0) {
                $requirement = [
                    'variant_id' => $variantId,
                    'product_id' => $data['product']->id,
                    'product_name' => $data['product']->name,
                    'color' => $data['variant']->color,
                    'current_stock' => $currentStock,
                    'total_demand' => $data['total_demand'],
                    'shortage' => $shortage,
                    'orders' => $data['orders'],
                    'priority' => $this->calculatePriority($data['orders']),
                    'is_composite' => $data['product']->is_composite,
                    'component_requirements' => []
                ];
                
                // Calculate component requirements for composite products
                if ($data['product']->is_composite) {
                    $requirement['component_requirements'] = $this->calculateComponentRequirements(
                        $data['product'], 
                        $shortage
                    );
                }
                
                $requirements[] = $requirement;
            }
        }
        
        // Sort by priority (earliest delivery date first)
        usort($requirements, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        
        return $requirements;
    }

    /**
     * Add manufactured stock with proper component deduction for composite products
     */
    public function addManufacturedStock(int $variantId, int $quantity, string $notes = null): bool
    {
        return DB::transaction(function () use ($variantId, $quantity, $notes) {
            $variant = ProductColorVariant::findOrFail($variantId);
            $product = $variant->product;
            
            // Check if this is a composite product
            if ($product->is_composite) {
                // For composite products: Use full assembly process that deducts components
                $stockService = app(StockService::class);
                
                Log::info('Starting composite product assembly', [
                    'variant_id' => $variantId,
                    'product_name' => $product->name,
                    'color' => $variant->color,
                    'quantity_to_assemble' => $quantity,
                    'components_count' => $product->components->count()
                ]);
                
                // This will:
                // 1. Validate component availability
                // 2. Deduct component stocks from their color variants
                // 3. Add assembled products to composite stock
                // 4. Log all movements with proper traceability
                $stockService->inwardColorVariantStock(
                    $variant, 
                    $quantity, 
                    $notes ?? "Assembly: Manufacturing {$quantity} units of {$product->name} ({$variant->color})"
                );
                
                Log::info('Composite product assembly completed', [
                    'variant_id' => $variantId,
                    'new_stock' => $variant->fresh()->quantity
                ]);
                
            } else {
                // For simple products: Direct stock increment (no components to deduct)
                $previousQty = $variant->quantity;
                $variant->increment('quantity', $quantity);
                
                // Log the stock movement
                $variant->product->stockLogs()->create([
                    'change_type' => 'inward',
                    'quantity' => $quantity,
                    'previous_quantity' => $previousQty,
                    'new_quantity' => $variant->fresh()->quantity,
                    'color_variant_id' => $variant->id,
                    'remarks' => $notes ?? "Manufacturing: Direct production of simple product"
                ]);

                Log::info('Simple product manufacturing stock added', [
                    'variant_id' => $variantId,
                    'product_name' => $variant->product->name,
                    'color' => $variant->color,
                    'quantity_added' => $quantity,
                    'new_stock' => $variant->fresh()->quantity
                ]);
            }

            return true;
        });
    }

    /**
     * Get requirement summary for dashboard
     */
    public function getRequirementSummary(): array
    {
        $requirements = $this->calculateDynamicRequirements();
        
        return [
            'total_shortages' => count($requirements),
            'total_shortage_quantity' => array_sum(array_column($requirements, 'shortage')),
            'urgent_requirements' => array_filter($requirements, function ($req) {
                return $req['priority'] <= 3; // Orders due within 3 days
            }),
            'pending_orders_count' => Order::where('status', 'pending')->count()
        ];
    }

    /**
     * Calculate priority based on delivery dates
     */
    private function calculatePriority(array $orders): int
    {
        $earliestDate = null;
        
        foreach ($orders as $order) {
            if ($order['delivery_date']) {
                $deliveryDate = \Carbon\Carbon::parse($order['delivery_date']);
                if (!$earliestDate || $deliveryDate->lt($earliestDate)) {
                    $earliestDate = $deliveryDate;
                }
            }
        }
        
        if (!$earliestDate) {
            return 999; // No delivery date = lowest priority
        }
        
        return max(1, $earliestDate->diffInDays(now()));
    }

    /**
     * Get assembly report for composite product to show what will be consumed
     */
    public function getAssemblyReport(int $variantId, int $quantity): array
    {
        $variant = ProductColorVariant::findOrFail($variantId);
        $product = $variant->product;
        
        if (!$product->is_composite) {
            return [
                'is_composite' => false,
                'message' => 'This is a simple product - no components will be consumed.'
            ];
        }

        $components = [];
        $canAssemble = true;
        $blockingComponents = [];

        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $available = $component->componentProduct->colorVariants->sum('quantity');
            $sufficient = $available >= $required;
            
            if (!$sufficient) {
                $canAssemble = false;
                $blockingComponents[] = $component->componentProduct->name;
            }

            $components[] = [
                'name' => $component->componentProduct->name,
                'needed_per_unit' => $component->quantity_needed,
                'total_needed' => $required,
                'available' => $available,
                'sufficient' => $sufficient,
                'shortage' => $sufficient ? 0 : ($required - $available)
            ];
        }

        return [
            'is_composite' => true,
            'can_assemble' => $canAssemble,
            'blocking_components' => $blockingComponents,
            'components' => $components,
            'total_components' => count($components),
            'message' => $canAssemble 
                ? "Assembly will consume components as listed below."
                : "Cannot assemble - insufficient stock for: " . implode(', ', $blockingComponents)
        ];
    }

    /**
     * Get orders that can be fulfilled (have sufficient stock)
     */
    public function getReadyOrders(): array
    {
        $readyOrders = [];
        
        $pendingOrders = Order::where('status', 'pending')
            ->with(['customer', 'items.product', 'items.colorVariant'])
            ->get();
        
        foreach ($pendingOrders as $order) {
            $canFulfill = true;
            $stockIssues = [];
            
            foreach ($order->items as $item) {
                $available = $item->colorVariant->quantity;
                $required = $item->quantity;
                
                if ($available < $required) {
                    $canFulfill = false;
                    $stockIssues[] = [
                        'product' => $item->product->name,
                        'color' => $item->colorVariant->color,
                        'needed' => $required,
                        'available' => $available,
                        'shortage' => $required - $available
                    ];
                }
            }
            
            if ($canFulfill) {
                $readyOrders[] = [
                    'order' => $order,
                    'can_fulfill' => true
                ];
            } else {
                $readyOrders[] = [
                    'order' => $order,
                    'can_fulfill' => false,
                    'stock_issues' => $stockIssues
                ];
            }
        }
        
        return $readyOrders;
    }

    /**
     * Calculate component requirements for composite products
     */
    private function calculateComponentRequirements($product, $quantityNeeded): array
    {
        $componentRequirements = [];
        
        if (!$product->is_composite) {
            return $componentRequirements;
        }
        
        // Load product components with their relationships
        $product->load(['components.componentProduct.colorVariants']);
        
        foreach ($product->components as $component) {
            $componentProduct = $component->componentProduct;
            $quantityPerUnit = $component->quantity_needed;
            $totalComponentsNeeded = $quantityNeeded * $quantityPerUnit;
            
            // Get total available stock for this component across all colors
            $totalAvailableStock = $componentProduct->colorVariants->sum('quantity');
            $componentShortage = max(0, $totalComponentsNeeded - $totalAvailableStock);
            
            // Get breakdown by color variant
            $colorBreakdown = [];
            foreach ($componentProduct->colorVariants as $colorVariant) {
                if ($colorVariant->quantity > 0) {
                    $colorBreakdown[] = [
                        'color' => $colorVariant->color,
                        'available' => $colorVariant->quantity,
                        'can_use' => min($colorVariant->quantity, $totalComponentsNeeded)
                    ];
                }
            }
            
            $componentRequirements[] = [
                'component_product_id' => $componentProduct->id,
                'component_name' => $componentProduct->name,
                'quantity_per_unit' => $quantityPerUnit,
                'total_needed' => $totalComponentsNeeded,
                'available_stock' => $totalAvailableStock,
                'shortage' => $componentShortage,
                'status' => $componentShortage > 0 ? 'insufficient' : 'sufficient',
                'color_breakdown' => $colorBreakdown
            ];
        }
        
        return $componentRequirements;
    }

    /**
     * Add stock for component products (simple products used in composite assembly)
     */
    public function addComponentStock(int $componentProductId, string $color, int $quantity, string $notes = null): bool
    {
        return DB::transaction(function () use ($componentProductId, $color, $quantity, $notes) {
            // Find the component product
            $componentProduct = \App\Models\Product::findOrFail($componentProductId);
            
            // Find or create the color variant for this component
            $colorVariant = \App\Models\ProductColorVariant::firstOrCreate([
                'product_id' => $componentProductId,
                'color' => $color
            ], [
                'quantity' => 0,
                'minimum_threshold' => 10 // Default threshold
            ]);
            
            // Add stock using simple increment (components are always simple products)
            $previousQty = $colorVariant->quantity;
            $colorVariant->increment('quantity', $quantity);
            
            // Log the stock movement
            $componentProduct->stockLogs()->create([
                'change_type' => 'inward',
                'quantity' => $quantity,
                'previous_quantity' => $previousQty,
                'new_quantity' => $colorVariant->fresh()->quantity,
                'color_variant_id' => $colorVariant->id,
                'remarks' => $notes ?? "Component Stock Addition: Direct procurement/manufacturing of component"
            ]);

            Log::info('Component stock added', [
                'component_product_id' => $componentProductId,
                'product_name' => $componentProduct->name,
                'color' => $color,
                'quantity_added' => $quantity,
                'new_stock' => $colorVariant->fresh()->quantity
            ]);

            return true;
        });
    }

    /**
     * Get all available colors for a component product
     */
    public function getComponentColors(int $componentProductId): array
    {
        $componentProduct = \App\Models\Product::findOrFail($componentProductId);
        
        return $componentProduct->colorVariants()
            ->orderBy('color')
            ->get()
            ->map(function ($variant) {
                return [
                    'color' => $variant->color,
                    'current_stock' => $variant->quantity,
                    'variant_id' => $variant->id
                ];
            })
            ->toArray();
    }
}