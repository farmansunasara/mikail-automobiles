<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductColorVariant;
use App\Models\ManufacturingRequirement;
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
            // If there are any outstanding manufacturing requirements for this order,
            // consider it not ready regardless of current stock calculations.
            $hasOpenMrForOrder = ManufacturingRequirement::where('order_id', $order->id)
                ->whereIn('status', ['open', 'in_production'])
                ->exists();

            $canFulfill = !$hasOpenMrForOrder;
            $stockIssues = [];
            
            foreach ($order->items as $item) {
                $available = $item->colorVariant->quantity;
                $required = $item->quantity;

                // Also consider any open manufacturing requirements for this variant.
                $openMrShortage = ManufacturingRequirement::where('status', 'open')
                    ->where('color_variant_id', $item->color_variant_id)
                    ->sum('shortage_quantity');

                // Effective available stock is current stock minus outstanding open MR shortage
                $effectiveAvailable = $available - $openMrShortage;

                if ($effectiveAvailable < $required) {
                    $stockIssues[] = [
                        'product' => $item->product->name,
                        'color' => $item->colorVariant->color,
                        'needed' => $required,
                        'available' => $available,
                        'outstanding_mr_shortage' => $openMrShortage,
                        'shortage' => $required - $effectiveAvailable
                    ];
                    // Ensure order-level flag remains false
                    $canFulfill = false;
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

    /**
     * Get manufacturing requirements for a specific product across all orders
     */
    public function getProductRequirements(int $productId, ?string $color = null): array
    {
        $query = Order::where('status', 'pending')
            ->with(['customer', 'items.product', 'items.colorVariant'])
            ->whereHas('items', function ($q) use ($productId, $color) {
                $q->where('product_id', $productId);
                if ($color) {
                    $q->whereHas('colorVariant', function ($cv) use ($color) {
                        $cv->where('color', $color);
                    });
                }
            });

        $orders = $query->get();
        
        $demand = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if ($item->product_id == $productId) {
                    $variantId = $item->color_variant_id;
                    $variant = $item->colorVariant;
                    
                    // Filter by color if specified
                    if ($color && $variant->color !== $color) {
                        continue;
                    }
                    
                    if (!isset($demand[$variantId])) {
                        $demand[$variantId] = [
                            'variant' => $variant,
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
                        'delivery_date' => $order->delivery_date,
                        'order_date' => $order->order_date,
                        'priority' => $this->calculateOrderPriority($order)
                    ];
                }
            }
        }
        
        // Calculate requirements
        $requirements = [];
        foreach ($demand as $variantId => $data) {
            $currentStock = $data['variant']->quantity;
            $shortage = max(0, $data['total_demand'] - $currentStock);
            
            if ($shortage > 0) {
                $requirements[] = [
                    'variant_id' => $variantId,
                    'product_id' => $data['product']->id,
                    'product_name' => $data['product']->name,
                    'color' => $data['variant']->color,
                    'current_stock' => $currentStock,
                    'total_demand' => $data['total_demand'],
                    'shortage' => $shortage,
                    'orders' => $data['orders'],
                    'order_count' => count($data['orders']),
                    'earliest_delivery' => $this->getEarliestDeliveryDate($data['orders']),
                    'latest_delivery' => $this->getLatestDeliveryDate($data['orders']),
                    'priority' => $this->calculatePriority($data['orders']),
                    'is_composite' => $data['product']->is_composite,
                    'component_requirements' => $data['product']->is_composite 
                        ? $this->calculateComponentRequirements($data['product'], $shortage)
                        : []
                ];
            }
        }
        
        // Sort by priority
        usort($requirements, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        
        return $requirements;
    }

    /**
     * Get aggregated requirements by product (all colors combined)
     */
    public function getProductAggregatedRequirements(int $productId): array
    {
        $requirements = $this->getProductRequirements($productId);
        
        if (empty($requirements)) {
            return [];
        }
        
        // Aggregate across all colors
        $totalDemand = array_sum(array_column($requirements, 'total_demand'));
        $totalShortage = array_sum(array_column($requirements, 'shortage'));
        $totalOrders = array_sum(array_column($requirements, 'order_count'));
        
        $allOrders = [];
        foreach ($requirements as $req) {
            $allOrders = array_merge($allOrders, $req['orders']);
        }
        
        return [
            'product_id' => $productId,
            'product_name' => $requirements[0]['product_name'],
            'total_demand' => $totalDemand,
            'total_shortage' => $totalShortage,
            'total_orders' => $totalOrders,
            'color_breakdown' => $requirements,
            'earliest_delivery' => $this->getEarliestDeliveryDate($allOrders),
            'latest_delivery' => $this->getLatestDeliveryDate($allOrders),
            'priority' => min(array_column($requirements, 'priority')),
            'is_composite' => $requirements[0]['is_composite'],
            'component_requirements' => $requirements[0]['is_composite'] 
                ? $this->calculateComponentRequirements($requirements[0]['product'], $totalShortage)
                : []
        ];
    }

    /**
     * Get manufacturing requirements grouped by product
     */
    public function getRequirementsByProduct(): array
    {
        $allRequirements = $this->calculateDynamicRequirements();
        
        $groupedByProduct = [];
        foreach ($allRequirements as $requirement) {
            $productId = $requirement['product_id'];
            if (!isset($groupedByProduct[$productId])) {
                $groupedByProduct[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $requirement['product_name'],
                    'total_demand' => 0,
                    'total_shortage' => 0,
                    'order_count' => 0,
                    'colors' => [],
                    'is_composite' => $requirement['is_composite']
                ];
            }
            
            $groupedByProduct[$productId]['total_demand'] += $requirement['total_demand'];
            $groupedByProduct[$productId]['total_shortage'] += $requirement['shortage'];
            $groupedByProduct[$productId]['order_count'] += count($requirement['orders']);
            $groupedByProduct[$productId]['colors'][] = [
                'color' => $requirement['color'],
                'demand' => $requirement['total_demand'],
                'shortage' => $requirement['shortage'],
                'orders' => $requirement['orders']
            ];
        }
        
        return array_values($groupedByProduct);
    }

    /**
     * Helper method to get earliest delivery date
     */
    private function getEarliestDeliveryDate(array $orders): ?string
    {
        $dates = array_filter(array_column($orders, 'delivery_date'));
        return empty($dates) ? null : min($dates);
    }

    /**
     * Helper method to get latest delivery date
     */
    private function getLatestDeliveryDate(array $orders): ?string
    {
        $dates = array_filter(array_column($orders, 'delivery_date'));
        return empty($dates) ? null : max($dates);
    }

    /**
     * Calculate order priority
     */
    private function calculateOrderPriority(Order $order): int
    {
        if (!$order->delivery_date) {
            return 999; // No delivery date = lowest priority
        }
        
        $daysUntilDelivery = now()->diffInDays($order->delivery_date, false);
        return max(1, $daysUntilDelivery);
    }
}