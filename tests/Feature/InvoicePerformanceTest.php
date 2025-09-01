<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\Category;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoicePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = app(InvoiceService::class);
    }

    /** @test */
    public function it_creates_gst_invoice_with_packaging_fees_efficiently()
    {
        // Setup test data
        $customer = Customer::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $colorVariant = ProductColorVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 100
        ]);

        $invoiceData = [
            'customer_id' => $customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'gst_rate' => 18,
            'discount_type' => 1,
            'discount_value' => 10,
            'packaging_fees' => 50,
            'items' => [
                [
                    'price' => 1000,
                    'variants' => [
                        [
                            'product_id' => $colorVariant->id,
                            'quantity' => 5
                        ]
                    ]
                ]
            ]
        ];

        $startTime = microtime(true);
        
        $invoice = $this->invoiceService->createGstInvoice($invoiceData);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Performance assertions
        $this->assertLessThan(500, $executionTime, 'Invoice creation should take less than 500ms');
        
        // Functional assertions
        $this->assertNotNull($invoice);
        $this->assertEquals(5000, $invoice->total_amount); // 5 * 1000
        $this->assertEquals(500, $invoice->discount_amount); // 10% of 5000
        $this->assertEquals(50, $invoice->packaging_fees);
        
        // Grand total calculation: (5000 - 500 + 50) * 1.18 = 4550 * 1.18 = 5369
        $expectedGrandTotal = (5000 - 500 + 50) * 1.18;
        $this->assertEquals($expectedGrandTotal, $invoice->grand_total);
    }

    /** @test */
    public function it_creates_non_gst_invoice_with_packaging_fees_efficiently()
    {
        // Setup test data
        $customer = Customer::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $colorVariant = ProductColorVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 100
        ]);

        $invoiceData = [
            'customer_id' => $customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'discount_type' => 0,
            'discount_value' => 200,
            'packaging_fees' => 75,
            'items' => [
                [
                    'price' => 800,
                    'variants' => [
                        [
                            'product_id' => $colorVariant->id,
                            'quantity' => 3
                        ]
                    ]
                ]
            ]
        ];

        $startTime = microtime(true);
        
        $invoice = $this->invoiceService->createNonGstInvoice($invoiceData);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Performance assertions
        $this->assertLessThan(300, $executionTime, 'Non-GST invoice creation should take less than 300ms');
        
        // Functional assertions
        $this->assertNotNull($invoice);
        $this->assertEquals(2400, $invoice->total_amount); // 3 * 800
        $this->assertEquals(200, $invoice->discount_amount); // Fixed discount
        $this->assertEquals(75, $invoice->packaging_fees);
        
        // Grand total: 2400 - 200 + 75 = 2275
        $this->assertEquals(2275, $invoice->grand_total);
    }
}
