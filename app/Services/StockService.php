<?php

namespace App\Services;

use App\Models\Product;
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
        });
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
}
