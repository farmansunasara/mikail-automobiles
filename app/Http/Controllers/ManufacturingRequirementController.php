<?php

namespace App\Http\Controllers;

use App\Services\RequirementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManufacturingRequirementController extends Controller
{
    protected $requirementService;

    public function __construct(RequirementService $requirementService)
    {
        $this->requirementService = $requirementService;
    }

    /**
     * Display manufacturing requirements (dynamic calculation)
     */
    public function index()
    {
        try {
            $requirements = $this->requirementService->calculateDynamicRequirements();
            $summary = $this->requirementService->getRequirementSummary();
            $readyOrders = $this->requirementService->getReadyOrders();

            return view('manufacturing.requirements.index', compact('requirements', 'summary', 'readyOrders'));
        } catch (\Exception $e) {
            Log::error('Failed to load manufacturing requirements', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to load manufacturing requirements: ' . $e->getMessage());
        }
    }

    /**
     * Add manufactured stock with component validation
     */
    public function addStock(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_color_variants,id',
            'quantity' => 'required|integer|min:1|max:9999',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Get assembly report first to validate component availability
            $assemblyReport = $this->requirementService->getAssemblyReport(
                $request->variant_id, 
                $request->quantity
            );

            // If it's a composite product and cannot assemble, show detailed error
            if ($assemblyReport['is_composite'] && !$assemblyReport['can_assemble']) {
                $errorMessage = "Cannot manufacture {$request->quantity} units. " . $assemblyReport['message'];
                
                Log::warning('Manufacturing blocked due to component shortage', [
                    'variant_id' => $request->variant_id,
                    'quantity' => $request->quantity,
                    'blocking_components' => $assemblyReport['blocking_components']
                ]);

                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->with('assembly_report', $assemblyReport);
            }

            $success = $this->requirementService->addManufacturedStock(
                $request->variant_id,
                $request->quantity,
                $request->notes
            );

            if ($success) {
                $successMessage = "Stock added successfully! Requirements updated automatically.";
                
                // Add component consumption details for composite products
                if ($assemblyReport['is_composite']) {
                    $componentDetails = "Components consumed: ";
                    $componentList = [];
                    foreach ($assemblyReport['components'] as $component) {
                        $componentList[] = "{$component['name']} ({$component['total_needed']} units)";
                    }
                    $successMessage .= " " . implode(', ', $componentList);
                }

                return redirect()->back()
                    ->with('success', $successMessage)
                    ->with('assembly_report', $assemblyReport);
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to add stock. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to add manufactured stock', [
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error adding stock: ' . $e->getMessage());
        }
    }

    /**
     * Get requirements data for AJAX
     */
    public function getRequirementsData()
    {
        try {
            $requirements = $this->requirementService->calculateDynamicRequirements();
            $summary = $this->requirementService->getRequirementSummary();

            return response()->json([
                'success' => true,
                'requirements' => $requirements,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ready orders for invoice generation
     */
    public function getReadyOrders()
    {
        try {
            $readyOrders = $this->requirementService->getReadyOrders();

            return response()->json([
                'success' => true,
                'ready_orders' => $readyOrders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assembly preview for AJAX requests
     */
    public function getAssemblyPreview(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_color_variants,id',
            'quantity' => 'required|integer|min:1|max:9999'
        ]);

        try {
            $assemblyReport = $this->requirementService->getAssemblyReport(
                $request->variant_id,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'report' => $assemblyReport
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating assembly preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add stock for component products
     */
    public function addComponentStock(Request $request)
    {
        $request->validate([
            'component_product_id' => 'required|exists:products,id',
            'color' => 'required|string|max:50',
            'quantity' => 'required|integer|min:1|max:9999',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $success = $this->requirementService->addComponentStock(
                $request->component_product_id,
                $request->color,
                $request->quantity,
                $request->notes
            );

            if ($success) {
                return redirect()->back()
                    ->with('success', 'Component stock added successfully! Manufacturing requirements updated.');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to add component stock. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to add component stock', [
                'component_product_id' => $request->component_product_id,
                'color' => $request->color,
                'quantity' => $request->quantity,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error adding component stock: ' . $e->getMessage());
        }
    }

    /**
     * Get component colors for AJAX requests
     */
    public function getComponentColors(Request $request)
    {
        $request->validate([
            'component_product_id' => 'required|exists:products,id'
        ]);

        try {
            $colors = $this->requirementService->getComponentColors($request->component_product_id);

            return response()->json([
                'success' => true,
                'colors' => $colors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching component colors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get manufacturing requirements for a specific product
     */
    public function getProductRequirements(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'color' => 'nullable|string|max:50'
        ]);

        try {
            $requirements = $this->requirementService->getProductRequirements(
                $request->product_id,
                $request->color
            );

            return response()->json([
                'success' => true,
                'requirements' => $requirements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get aggregated requirements for a product (all colors)
     */
    public function getProductAggregatedRequirements(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $requirements = $this->requirementService->getProductAggregatedRequirements(
                $request->product_id
            );

            return response()->json([
                'success' => true,
                'requirements' => $requirements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requirements grouped by product
     */
    public function getRequirementsByProduct()
    {
        try {
            $requirements = $this->requirementService->getRequirementsByProduct();

            return response()->json([
                'success' => true,
                'requirements' => $requirements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}