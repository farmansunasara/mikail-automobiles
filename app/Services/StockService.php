<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Handle inward stock movement.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @return void
     */
    public function inwardStock(Product $product, int $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($product, $quantity, $notes) {
            if ($product->is_composite) {
                $this->handleCompositeInward($product, $quantity, $notes);
            } else {
                $this->handleSimpleInward($product, $quantity, $notes);
            }
        });
    }

    /**
     * Handle inward stock movement for simple product.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @return void
     */
    protected function handleSimpleInward(Product $product, int $quantity, ?string $notes = null): void
    {
        $previousQuantity = $product->quantity;
        $product->increment('quantity', $quantity);
        $newQuantity = $product->fresh()->quantity;

        $product->stockLogs()->create([
            'change_type' => 'inward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'remarks' => $notes,
        ]);
    }

    /**
     * Handle inward stock movement for composite product.
     * This will automatically consume components to assemble the composite product.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    protected function handleCompositeInward(Product $product, int $quantity, ?string $notes = null): void
    {
        // Check if we have enough components to assemble the composite products
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            if ($component->componentProduct->quantity < $required) {
                throw new \Exception("Cannot assemble {$quantity} units of {$product->name}. Not enough stock for component: {$component->componentProduct->name}. Available: {$component->componentProduct->quantity}, Required: {$required}");
            }
        }

        // First, consume the components
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $this->handleSimpleOutward($component->componentProduct, $required, "Component consumed for assembling {$product->name}. {$notes}");
        }

        // Then, add the assembled composite products to stock
        $previousQuantity = $product->quantity;
        $product->increment('quantity', $quantity);
        $newQuantity = $product->fresh()->quantity;

        $product->stockLogs()->create([
            'change_type' => 'inward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'remarks' => "Assembled from components. {$notes}",
        ]);
    }

    /**
     * Handle outward stock movement.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    public function outwardStock(Product $product, int $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($product, $quantity, $notes) {
            if ($product->is_composite) {
                $this->handleCompositeOutward($product, $quantity, $notes);
            } else {
                $this->handleSimpleOutward($product, $quantity, $notes);
            }
        });
    }

    /**
     * Handle stock reduction for a simple product.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    protected function handleSimpleOutward(Product $product, int $quantity, ?string $notes = null): void
    {
        if ($product->quantity < $quantity) {
            throw new \Exception("Not enough stock for product: {$product->name}. Available: {$product->quantity}, Required: {$quantity}");
        }

        $previousQuantity = $product->quantity;
        $product->decrement('quantity', $quantity);
        $newQuantity = $product->fresh()->quantity;

        $product->stockLogs()->create([
            'change_type' => 'outward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'remarks' => $notes,
        ]);
    }

    /**
     * Handle stock reduction for a composite product and its components.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    protected function handleCompositeOutward(Product $product, int $quantity, ?string $notes = null): void
    {
        if ($product->quantity < $quantity) {
            throw new \Exception("Not enough stock for composite product: {$product->name}. Available: {$product->quantity}, Required: {$quantity}");
        }

        // Check stock for all components first
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            if ($component->componentProduct->quantity < $required) {
                throw new \Exception("Not enough stock for component: {$component->componentProduct->name}. Available: {$component->componentProduct->quantity}, Required: {$required}");
            }
        }

        // Decrement composite product stock
        $previousQuantity = $product->quantity;
        $product->decrement('quantity', $quantity);
        $newQuantity = $product->fresh()->quantity;
        
        $product->stockLogs()->create([
            'change_type' => 'outward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'remarks' => $notes,
        ]);
        
        // Decrement component stocks
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $this->handleSimpleOutward($component->componentProduct, $required, "Component for {$product->name} sale. {$notes}");
        }
    }

    /**
     * Handle inward stock movement for color variant.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     */
    public function inwardColorVariantStock(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($colorVariant, $quantity, $notes) {
            $product = $colorVariant->product;
            
            if ($product->is_composite) {
                $this->handleCompositeColorVariantInward($colorVariant, $quantity, $notes);
            } else {
                $this->handleSimpleColorVariantInward($colorVariant, $quantity, $notes);
            }
        });
    }

    /**
     * Handle inward stock movement for simple color variant.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     */
    protected function handleSimpleColorVariantInward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        $previousQuantity = $colorVariant->quantity;
        $colorVariant->increment('quantity', $quantity);
        $newQuantity = $colorVariant->fresh()->quantity;

        $colorVariant->product->stockLogs()->create([
            'change_type' => 'inward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'color_variant_id' => $colorVariant->id,
            'remarks' => $notes,
        ]);
    }

    /**
     * Handle inward stock movement for composite color variant.
     * This will automatically consume components to assemble the composite product.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    protected function handleCompositeColorVariantInward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        $product = $colorVariant->product;
        
        // Check if we have enough components to assemble the composite products
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
            if ($componentTotalStock < $required) {
                throw new \Exception("Cannot assemble {$quantity} units of {$product->name} ({$colorVariant->color}). Not enough stock for component: {$component->componentProduct->name}. Available: {$componentTotalStock}, Required: {$required}");
            }
        }

        // First, consume the components
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $this->deductFromComponentColorVariants(
                $component->componentProduct, 
                $required, 
                "Component consumed for assembling {$product->name} ({$colorVariant->color}). {$notes}"
            );
        }

        // Then, add the assembled composite products to stock
        $previousQuantity = $colorVariant->quantity;
        $colorVariant->increment('quantity', $quantity);
        $newQuantity = $colorVariant->fresh()->quantity;

        $colorVariant->product->stockLogs()->create([
            'change_type' => 'inward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'color_variant_id' => $colorVariant->id,
            'remarks' => "Assembled from components. {$notes}",
        ]);
    }

    /**
     * Handle outward stock movement for color variant.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    public function outwardColorVariantStock(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($colorVariant, $quantity, $notes) {
            $product = $colorVariant->product;
            
            if ($product->is_composite) {
                $this->handleCompositeColorVariantOutward($colorVariant, $quantity, $notes);
            } else {
                $this->handleSimpleColorVariantOutward($colorVariant, $quantity, $notes);
            }
        });
    }

    /**
     * Handle outward stock movement for color variant (sale only - no component deduction).
     * This is used for invoice generation where components were already consumed during assembly.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    public function outwardColorVariantStockSaleOnly(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($colorVariant, $quantity, $notes) {
            // For both simple and composite products, only reduce the product stock
            // Components are not touched since they were already consumed during assembly
            $this->handleSimpleColorVariantOutward($colorVariant, $quantity, $notes);
        });
    }

    /**
     * Handle stock reduction for a simple color variant.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    protected function handleSimpleColorVariantOutward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        if ($colorVariant->quantity < $quantity) {
            throw new \Exception("Not enough stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
        }

        $previousQuantity = $colorVariant->quantity;
        $colorVariant->decrement('quantity', $quantity);
        $newQuantity = $colorVariant->fresh()->quantity;

        $colorVariant->product->stockLogs()->create([
            'change_type' => 'outward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'color_variant_id' => $colorVariant->id,
            'remarks' => $notes,
        ]);
    }

    /**
     * Handle stock reduction for a composite color variant and its components.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws \Exception
     */
    protected function handleCompositeColorVariantOutward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        $product = $colorVariant->product;
        
        if ($colorVariant->quantity < $quantity) {
            throw new \Exception("Not enough stock for composite product: {$product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
        }

        // Check stock for all components first
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
            if ($componentTotalStock < $required) {
                throw new \Exception("Not enough stock for component: {$component->componentProduct->name}. Available: {$componentTotalStock}, Required: {$required}");
            }
        }

        // Decrement composite product color variant stock
        $previousQuantity = $colorVariant->quantity;
        $colorVariant->decrement('quantity', $quantity);
        $newQuantity = $colorVariant->fresh()->quantity;
        
        $product->stockLogs()->create([
            'change_type' => 'outward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'color_variant_id' => $colorVariant->id,
            'remarks' => $notes,
        ]);
        
        // Decrement component stocks (from their color variants)
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $this->deductFromComponentColorVariants($component->componentProduct, $required, "Component for {$product->name} ({$colorVariant->color}) sale. {$notes}");
        }
    }

    /**
     * Deduct stock from component product's color variants.
     *
     * @param Product $componentProduct
     * @param int $totalRequired
     * @param string $notes
     * @return void
     * @throws \Exception
     */
    protected function deductFromComponentColorVariants(Product $componentProduct, int $totalRequired, string $notes): void
    {
        $remaining = $totalRequired;
        $colorVariants = $componentProduct->colorVariants()->where('quantity', '>', 0)->orderBy('quantity', 'desc')->get();
        
        foreach ($colorVariants as $variant) {
            if ($remaining <= 0) break;
            
            $deductAmount = min($variant->quantity, $remaining);
            $this->handleSimpleColorVariantOutward($variant, $deductAmount, $notes);
            $remaining -= $deductAmount;
        }
        
        if ($remaining > 0) {
            throw new \Exception("Could not deduct sufficient stock from component: {$componentProduct->name}. Still need: {$remaining}");
        }
    }
}
