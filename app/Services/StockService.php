<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\StockLog;
use App\Models\Color;
use App\Models\ColorStockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StockService
{
    /**
     * Restore stock for a color variant (sale only - no component restoration).
     * This is used for invoice editing where components were already consumed during assembly.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws Exception
     */
    public function inwardColorVariantStockSaleOnly(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        DB::transaction(function () use ($colorVariant, $quantity, $notes) {
            $previousQuantity = $colorVariant->quantity;
            $colorVariant->increment('quantity', $quantity);
            $colorVariant->refresh();
            $colorVariant->product->stockLogs()->create([
                'change_type' => 'inward',
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $colorVariant->quantity,
                'color_variant_id' => $colorVariant->id,
                'remarks' => $notes,
            ]);
        });
    }
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
     * @throws Exception
     */
    protected function handleCompositeInward(Product $product, int $quantity, ?string $notes = null): void
    {
        // Check if we have enough components to assemble the composite products
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            if ($component->componentProduct->quantity < $required) {
                throw new Exception("Cannot assemble {$quantity} units of {$product->name}. Not enough stock for component: {$component->componentProduct->name}. Available: {$component->componentProduct->quantity}, Required: {$required}");
            }
        }

        // First, consume the components (product-level decrement)
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
     * @throws Exception
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
     * @throws Exception
     */
    protected function handleSimpleOutward(Product $product, int $quantity, ?string $notes = null): void
    {
        if ($product->quantity < $quantity) {
            throw new Exception("Not enough stock for product: {$product->name}. Available: {$product->quantity}, Required: {$quantity}");
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
     * @throws Exception
     */
    protected function handleCompositeOutward(Product $product, int $quantity, ?string $notes = null): void
    {
        if ($product->quantity < $quantity) {
            throw new Exception("Not enough stock for composite product: {$product->name}. Available: {$product->quantity}, Required: {$quantity}");
        }

        // Check stock for all components first
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            if ($component->componentProduct->quantity < $required) {
                throw new Exception("Not enough stock for component: {$component->componentProduct->name}. Available: {$component->componentProduct->quantity}, Required: {$required}");
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
        Log::channel('stock')->info('STARTING INWARD COLOR VARIANT STOCK', [
            'variant_id' => $colorVariant->id,
            'product_id' => $colorVariant->product->id,
            'product_name' => $colorVariant->product->name,
            'is_composite' => $colorVariant->product->is_composite,
            'color' => $colorVariant->color,
            'initial_qty' => $colorVariant->quantity,
            'increment_by' => $quantity
        ]);

        // Check if this is a composite product
        if ($colorVariant->product->is_composite) {
            // Handle composite product assembly
            $this->handleCompositeColorVariantInward($colorVariant, $quantity, $notes);
        } else {
            // Handle simple product stock increase
            $this->handleSimpleColorVariantInward($colorVariant, $quantity, $notes);
        }
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
    Log::channel('stock')->info('STARTING SIMPLE COLOR VARIANT INWARD', [
        'variant_id' => $colorVariant->id,
        'product_name' => $colorVariant->product->name,
        'color' => $colorVariant->color,
        'current_quantity' => $colorVariant->quantity,
        'quantity_to_add' => $quantity
    ]);

    DB::beginTransaction();
    try {
        $previousQuantity = $colorVariant->quantity;
        $colorVariant->increment('quantity', $quantity);
        $colorVariant->refresh();
        
        Log::channel('stock')->info('SIMPLE COLOR VARIANT QUANTITY UPDATED', [
            'variant_id' => $colorVariant->id,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $colorVariant->quantity,
            'increment' => $quantity
        ]);

        // Check and deduct color stock if needed
        if ($colorVariant->colorModel && $colorVariant->color_usage_grams > 0) {
            Log::channel('stock')->info('DEDUCTING COLOR STOCK FOR SIMPLE PRODUCT', [
                'color_id' => $colorVariant->colorModel->id,
                'color_name' => $colorVariant->colorModel->name,
                'usage_per_unit' => $colorVariant->color_usage_grams,
                'total_usage' => $colorVariant->color_usage_grams * $quantity
            ]);
            $this->deductColorStock($colorVariant, $quantity, $notes);
        }

        $colorVariant->product->stockLogs()->create([
            'change_type' => 'inward',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $colorVariant->quantity,
            'color_variant_id' => $colorVariant->id,
            'remarks' => $notes,
        ]);

        Log::channel('stock')->info('SIMPLE COLOR VARIANT INWARD COMPLETED', [
            'variant_id' => $colorVariant->id,
            'final_quantity' => $colorVariant->quantity
        ]);

        DB::commit();
    } catch (Exception $e) {
        DB::rollBack();
        Log::channel('stock')->error('SIMPLE COLOR VARIANT INWARD FAILED', [
            'variant_id' => $colorVariant->id,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}


    /**
     * Handle inward stock movement for composite color variant.
     * This will automatically consume components to assemble the composite product.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws Exception
     */
    protected function handleCompositeColorVariantInward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        $product = $colorVariant->product;

        Log::channel('stock')->info('STARTING COMPOSITE COLOR VARIANT INWARD', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'color_variant_id' => $colorVariant->id,
            'color' => $colorVariant->color,
            'current_quantity' => $colorVariant->quantity,
            'quantity_to_add' => $quantity,
            'components_count' => $product->components->count()
        ]);

        // Check if we have enough components to assemble the composite products
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
            
            Log::channel('stock')->info('CHECKING COMPONENT AVAILABILITY', [
                'component_product_id' => $component->componentProduct->id,
                'component_name' => $component->componentProduct->name,
                'quantity_needed_per_unit' => $component->quantity_needed,
                'total_required' => $required,
                'available_stock' => $componentTotalStock
            ]);
            
            if ($componentTotalStock < $required) {
                throw new Exception("Cannot assemble {$quantity} units of {$product->name} ({$colorVariant->color}). Not enough stock for component: {$component->componentProduct->name}. Available: {$componentTotalStock}, Required: {$required}");
            }
        }

        // Check if we have enough color stock for the composite product
        if ($colorVariant->colorModel && $colorVariant->color_usage_grams > 0) {
            $requiredColorGrams = $colorVariant->color_usage_grams * $quantity;
            if (!$colorVariant->colorModel->hasSufficientStock($requiredColorGrams)) {
                throw new Exception("Insufficient color stock for {$colorVariant->colorModel->name}. Available: {$colorVariant->colorModel->stock_grams}g, Required: {$requiredColorGrams}g");
            }
        }

        // Use database transaction to ensure all operations complete or none do
        DB::beginTransaction();
        try {
            // First, consume the components (from their color variants)
            foreach ($product->components as $component) {
                $required = $component->quantity_needed * $quantity;
                
                Log::channel('stock')->info('DEDUCTING COMPONENT STOCK', [
                    'component_product_id' => $component->componentProduct->id,
                    'component_name' => $component->componentProduct->name,
                    'required_quantity' => $required
                ]);
                
                $this->deductFromComponentColorVariants(
                    $component->componentProduct,
                    $required,
                    "Component consumed for assembling {$product->name} ({$colorVariant->color}). {$notes}"
                );
            }

            // Second, deduct color stock if needed for the composite product
            if ($colorVariant->colorModel && $colorVariant->color_usage_grams > 0) {
                Log::channel('stock')->info('DEDUCTING COLOR STOCK', [
                    'color_id' => $colorVariant->colorModel->id,
                    'color_name' => $colorVariant->colorModel->name,
                    'usage_per_unit' => $colorVariant->color_usage_grams,
                    'total_usage' => $colorVariant->color_usage_grams * $quantity
                ]);
                
                $this->deductColorStock($colorVariant, $quantity, $notes);
            }

            // Finally, add the assembled composite products to stock (color variant)
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

            Log::channel('stock')->info('COMPOSITE ASSEMBLY COMPLETED', [
                'product_id' => $product->id,
                'color_variant_id' => $colorVariant->id,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'quantity_added' => $quantity
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('stock')->error('COMPOSITE ASSEMBLY FAILED', [
                'product_id' => $product->id,
                'color_variant_id' => $colorVariant->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle outward stock movement for color variant.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws Exception
     */
       public function outwardColorVariantStock(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        Log::channel('stock')->info('STARTING OUTWARD STOCK', [
            'variant_id' => $colorVariant->id,
            'initial_qty' => $colorVariant->quantity,
            'decrement_by' => $quantity
        ]);

        DB::beginTransaction();
        try {
            $previousQuantity = $colorVariant->quantity;
            
            if ($colorVariant->quantity < $quantity) {
                throw new \Exception("Not enough stock available. Requested: {$quantity}, Available: {$colorVariant->quantity}");
            }

            $colorVariant->decrement('quantity', $quantity);
            $colorVariant->refresh();

            Log::channel('stock')->info('QUANTITY DECREMENTED', [
                'variant_id' => $colorVariant->id,
                'before' => $previousQuantity,
                'after' => $colorVariant->quantity,
                'expected' => $previousQuantity - $quantity
            ]);

            $colorVariant->product->stockLogs()->create([
                'change_type' => 'outward',
                'quantity' => $quantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $colorVariant->quantity,
                'color_variant_id' => $colorVariant->id,
                'remarks' => $notes,
            ]);

            DB::commit();
            Log::channel('stock')->info('TRANSACTION COMMITTED');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stock')->error('OUTWARD STOCK FAILED', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }



    /**
     * Handle outward stock movement for color variant (sale only - no component deduction).
     * This is used for invoice generation where components were already consumed during assembly.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
  protected function handleSimpleColorVariantOutward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
{
    if ($colorVariant->quantity < $quantity) {
        throw new \Exception("Not enough stock for {$colorVariant->product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
    }

    \Log::info("[Outward] Before decrement: Variant ID {$colorVariant->id}, quantity: {$colorVariant->quantity}");
    $colorVariant->decrement('quantity', $quantity);
    $colorVariant->refresh();
    \Log::info("[Outward] After decrement: Variant ID {$colorVariant->id}, quantity: {$colorVariant->quantity}");

    $previousQuantity = $colorVariant->quantity + $quantity;
    $newQuantity = $colorVariant->quantity;

    $colorVariant->product->stockLogs()->create([
        'change_type' => 'outward',
        'quantity' => $quantity,
        'previous_quantity' => $previousQuantity,
        'new_quantity' => $newQuantity,
        'color_variant_id' => $colorVariant->id,
        'remarks' => $notes,
    ]);

    // Notify admins if this variant is now below its minimum threshold (variant or product)
    $threshold = $colorVariant->minimum_threshold ?? 0;
    if ($threshold > 0 && $colorVariant->quantity < $threshold) {
        \App\Notifications\LowStockProductNotification::notifyAdmins($colorVariant);
    }
}

    /**
     * Handle stock reduction for a composite color variant and its components.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws Exception
     */
    protected function handleCompositeColorVariantOutward(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        $product = $colorVariant->product;

        if ($colorVariant->quantity < $quantity) {
            throw new Exception("Not enough stock for composite product: {$product->name} ({$colorVariant->color}). Available: {$colorVariant->quantity}, Required: {$quantity}");
        }

        // Check stock for all components first
        foreach ($product->components as $component) {
            $required = $component->quantity_needed * $quantity;
            $componentTotalStock = $component->componentProduct->colorVariants->sum('quantity');
            if ($componentTotalStock < $required) {
                throw new Exception("Not enough stock for component: {$component->componentProduct->name}. Available: {$componentTotalStock}, Required: {$required}");
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
     * @throws Exception
     */
    protected function deductFromComponentColorVariants(Product $componentProduct, int $totalRequired, string $notes): void
    {
        Log::channel('stock')->info('STARTING COMPONENT DEDUCTION', [
            'component_product_id' => $componentProduct->id,
            'component_name' => $componentProduct->name,
            'total_required' => $totalRequired,
            'available_variants' => $componentProduct->colorVariants->count()
        ]);

        $remaining = $totalRequired;
        $colorVariants = $componentProduct->colorVariants()->where('quantity', '>', 0)->orderBy('quantity', 'desc')->get();

        Log::channel('stock')->info('FOUND COLOR VARIANTS WITH STOCK', [
            'variants_with_stock' => $colorVariants->count(),
            'variants_data' => $colorVariants->map(function($v) {
                return [
                    'id' => $v->id,
                    'color' => $v->color,
                    'quantity' => $v->quantity
                ];
            })->toArray()
        ]);

        foreach ($colorVariants as $variant) {
            if ($remaining <= 0) break;

            $deductAmount = min($variant->quantity, $remaining);
            
            Log::channel('stock')->info('DEDUCTING FROM COLOR VARIANT', [
                'variant_id' => $variant->id,
                'variant_color' => $variant->color,
                'variant_current_quantity' => $variant->quantity,
                'deduct_amount' => $deductAmount,
                'remaining_after' => $remaining - $deductAmount
            ]);
            
            $this->handleSimpleColorVariantOutward($variant, $deductAmount, $notes);
            $remaining -= $deductAmount;
            
            // Refresh the variant to see the updated quantity
            $variant->refresh();
            
            Log::channel('stock')->info('AFTER DEDUCTION', [
                'variant_id' => $variant->id,
                'variant_new_quantity' => $variant->quantity,
                'remaining_to_deduct' => $remaining
            ]);
        }

        if ($remaining > 0) {
            Log::channel('stock')->error('INSUFFICIENT COMPONENT STOCK', [
                'component_product_id' => $componentProduct->id,
                'component_name' => $componentProduct->name,
                'still_needed' => $remaining,
                'total_required' => $totalRequired
            ]);
            throw new Exception("Could not deduct sufficient stock from component: {$componentProduct->name}. Still need: {$remaining}");
        }
        
        Log::channel('stock')->info('COMPONENT DEDUCTION COMPLETED', [
            'component_product_id' => $componentProduct->id,
            'component_name' => $componentProduct->name,
            'total_deducted' => $totalRequired
        ]);
    }

    /**
     * Deduct color stock when manufacturing products.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @param string|null $notes
     * @return void
     * @throws Exception
     */
    protected function deductColorStock(ProductColorVariant $colorVariant, int $quantity, ?string $notes = null): void
    {
        if (!$colorVariant->colorModel || $colorVariant->color_usage_grams <= 0) {
            return; // No color deduction needed
        }

        $requiredColorGrams = $colorVariant->color_usage_grams * $quantity;
        $color = $colorVariant->colorModel;

        if (!$color->hasSufficientStock($requiredColorGrams)) {
            throw new Exception("Insufficient color stock for {$color->name}. Available: {$color->stock_grams}g, Required: {$requiredColorGrams}g");
        }

        $this->outwardColorStock(
            $color,
            $requiredColorGrams,
            "Used for manufacturing {$quantity} units of {$colorVariant->product->name} ({$colorVariant->color}). {$notes}",
            'product_manufacturing',
            $colorVariant->id
        );
    }

    /**
     * Handle inward color stock movement.
     *
     * @param Color $color
     * @param float $quantityGrams
     * @param string|null $remarks
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     */
    public function inwardColorStock(Color $color, float $quantityGrams, ?string $remarks = null, ?string $referenceType = null, ?int $referenceId = null): void
    {
        DB::transaction(function () use ($color, $quantityGrams, $remarks, $referenceType, $referenceId) {
            $previousStock = $color->stock_grams;
            $color->increment('stock_grams', $quantityGrams);
            $newStock = $color->fresh()->stock_grams;

            ColorStockLog::create([
                'color_id' => $color->id,
                'change_type' => 'inward',
                'quantity_grams' => $quantityGrams,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'remarks' => $remarks,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    /**
     * Handle outward color stock movement.
     *
     * @param Color $color
     * @param float $quantityGrams
     * @param string|null $remarks
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     * @throws Exception
     */
    public function outwardColorStock(Color $color, float $quantityGrams, ?string $remarks = null, ?string $referenceType = null, ?int $referenceId = null): void
    {
        DB::transaction(function () use ($color, $quantityGrams, $remarks, $referenceType, $referenceId) {
            if (!$color->hasSufficientStock($quantityGrams)) {
                throw new Exception("Insufficient color stock for {$color->name}. Available: {$color->stock_grams}g, Required: {$quantityGrams}g");
            }

            $previousStock = $color->stock_grams;
            $color->decrement('stock_grams', $quantityGrams);
            $newStock = $color->fresh()->stock_grams;

            ColorStockLog::create([
                'color_id' => $color->id,
                'change_type' => 'outward',
                'quantity_grams' => $quantityGrams,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'remarks' => $remarks,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    /**
     * Check if color variant has sufficient color stock for production.
     *
     * @param ProductColorVariant $colorVariant
     * @param int $quantity
     * @return bool
     */
    public function checkColorStockAvailability(ProductColorVariant $colorVariant, int $quantity): bool
    {
        if (!$colorVariant->colorModel || $colorVariant->color_usage_grams <= 0) {
            return true; // No color restriction
        }

        $requiredColorGrams = $colorVariant->color_usage_grams * $quantity;
        return $colorVariant->colorModel->hasSufficientStock($requiredColorGrams);
    }
}
