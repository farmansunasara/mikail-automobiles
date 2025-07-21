<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\Category;
use App\Models\Subcategory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and subcategories
        $activaCategory = Category::where('name', 'Honda Activa 6G')->first();
        $electricalSubcat = Subcategory::where('name', 'Electrical')->where('category_id', $activaCategory->id)->first();
        $bodySubcat = Subcategory::where('name', 'Body Parts')->where('category_id', $activaCategory->id)->first();
        $engineSubcat = Subcategory::where('name', 'Engine Parts')->where('category_id', $activaCategory->id)->first();

        // Simple Products
        $products = [
            [
                'name' => 'Headlight Assembly',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $electricalSubcat->id,
                'color' => 'Clear',
                'quantity' => 50,
                'price' => 1200.00,
                'hsn_code' => '85122000',
                'gst_rate' => 18.00,
                'is_composite' => false
            ],
            [
                'name' => 'Tail Light',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $electricalSubcat->id,
                'color' => 'Red',
                'quantity' => 75,
                'price' => 450.00,
                'hsn_code' => '85122000',
                'gst_rate' => 18.00,
                'is_composite' => false
            ],
            [
                'name' => 'Floor Mat',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Black',
                'quantity' => 30,
                'price' => 250.00,
                'hsn_code' => '40169300',
                'gst_rate' => 18.00,
                'is_composite' => false
            ],
            [
                'name' => 'Side Panel Left',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'White',
                'quantity' => 25,
                'price' => 800.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Side Panel Right',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'White',
                'quantity' => 25,
                'price' => 800.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Air Filter',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $engineSubcat->id,
                'color' => 'White',
                'quantity' => 100,
                'price' => 180.00,
                'hsn_code' => '84213100',
                'gst_rate' => 18.00,
                'is_composite' => false
            ],
            [
                'name' => 'Spark Plug',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $engineSubcat->id,
                'color' => 'Silver',
                'quantity' => 200,
                'price' => 120.00,
                'hsn_code' => '85111000',
                'gst_rate' => 18.00,
                'is_composite' => false
            ],
            [
                'name' => 'Engine Oil',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $engineSubcat->id,
                'color' => 'Golden',
                'quantity' => 80,
                'price' => 350.00,
                'hsn_code' => '27101981',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Brake Pad Set',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Black',
                'quantity' => 60,
                'price' => 280.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Chain Set',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $engineSubcat->id,
                'color' => 'Silver',
                'quantity' => 40,
                'price' => 650.00,
                'hsn_code' => '87149900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            // Color variations for mudguards (client requirement)
            [
                'name' => 'Front Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Black',
                'quantity' => 35,
                'price' => 320.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Front Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Red',
                'quantity' => 28,
                'price' => 320.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Front Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Blue',
                'quantity' => 22,
                'price' => 320.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Front Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'White',
                'quantity' => 18,
                'price' => 320.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Rear Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Black',
                'quantity' => 30,
                'price' => 280.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Rear Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Red',
                'quantity' => 25,
                'price' => 280.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Rear Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Blue',
                'quantity' => 20,
                'price' => 280.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ],
            [
                'name' => 'Rear Mudguard',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'White',
                'quantity' => 15,
                'price' => 280.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create composite products
        $compositeProducts = [
            [
                'name' => 'Complete Flooring Kit',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $bodySubcat->id,
                'color' => 'Mixed',
                'quantity' => 10,
                'price' => 2100.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => true,
                'components' => [
                    ['component_name' => 'Floor Mat', 'quantity_needed' => 1],
                    ['component_name' => 'Side Panel Left', 'quantity_needed' => 1],
                    ['component_name' => 'Side Panel Right', 'quantity_needed' => 1]
                ]
            ],
            [
                'name' => 'Service Kit Basic',
                'category_id' => $activaCategory->id,
                'subcategory_id' => $engineSubcat->id,
                'color' => 'Mixed',
                'quantity' => 15,
                'price' => 850.00,
                'hsn_code' => '84213100',
                'gst_rate' => 18.00,
                'is_composite' => true,
                'components' => [
                    ['component_name' => 'Air Filter', 'quantity_needed' => 1],
                    ['component_name' => 'Spark Plug', 'quantity_needed' => 1],
                    ['component_name' => 'Engine Oil', 'quantity_needed' => 1],
                    ['component_name' => 'Brake Pad Set', 'quantity_needed' => 1]
                ]
            ]
        ];

        foreach ($compositeProducts as $compositeData) {
            $components = $compositeData['components'];
            unset($compositeData['components']);
            
            $compositeProduct = Product::create($compositeData);

            // Add components
            foreach ($components as $componentData) {
                $componentProduct = Product::where('name', $componentData['component_name'])->first();
                if ($componentProduct) {
                    ProductComponent::create([
                        'parent_product_id' => $compositeProduct->id,
                        'component_product_id' => $componentProduct->id,
                        'quantity_needed' => $componentData['quantity_needed']
                    ]);
                }
            }
        }
    }
}
