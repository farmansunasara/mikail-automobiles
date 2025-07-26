<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\ProductColorVariant;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeederUpdated extends Seeder
{
    /**
     * Seed the application's database with color variants support.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@almikailautomobile.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create categories
        $categories = [
            ['name' => 'Honda Activa 6G', 'description' => 'Parts for Honda Activa 6G'],
            ['name' => 'Honda Activa 5G', 'description' => 'Parts for Honda Activa 5G'],
            ['name' => 'TVS Jupiter', 'description' => 'Parts for TVS Jupiter'],
            ['name' => 'Bajaj Pulsar', 'description' => 'Parts for Bajaj Pulsar'],
            ['name' => 'Hero Splendor', 'description' => 'Parts for Hero Splendor'],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create subcategories
        $subcategories = [
            // Honda Activa 6G subcategories
            ['category_id' => 1, 'name' => 'Electrical'],
            ['category_id' => 1, 'name' => 'Body Parts'],
            ['category_id' => 1, 'name' => 'Engine Parts'],
            ['category_id' => 1, 'name' => 'Brake System'],
            ['category_id' => 1, 'name' => 'Suspension'],
            
            // Honda Activa 5G subcategories
            ['category_id' => 2, 'name' => 'Electrical'],
            ['category_id' => 2, 'name' => 'Body Parts'],
            ['category_id' => 2, 'name' => 'Engine Parts'],
            ['category_id' => 2, 'name' => 'Brake System'],
            
            // TVS Jupiter subcategories
            ['category_id' => 3, 'name' => 'Electrical'],
            ['category_id' => 3, 'name' => 'Body Parts'],
            ['category_id' => 3, 'name' => 'Engine Parts'],
            ['category_id' => 3, 'name' => 'Transmission'],
            
            // Bajaj Pulsar subcategories
            ['category_id' => 4, 'name' => 'Electrical'],
            ['category_id' => 4, 'name' => 'Body Parts'],
            ['category_id' => 4, 'name' => 'Engine Parts'],
            ['category_id' => 4, 'name' => 'Fuel System'],
            
            // Hero Splendor subcategories
            ['category_id' => 5, 'name' => 'Electrical'],
            ['category_id' => 5, 'name' => 'Body Parts'],
            ['category_id' => 5, 'name' => 'Engine Parts'],
            ['category_id' => 5, 'name' => 'Clutch System'],
        ];

        foreach ($subcategories as $subcategoryData) {
            Subcategory::create($subcategoryData);
        }

        // Create sample products with color variants
        $productsWithVariants = [
            [
                'product' => [
                    'name' => 'Headlight Assembly',
                    'category_id' => 1,
                    'subcategory_id' => 1,
                    'price' => 1200.00,
                    'hsn_code' => '85122000',
                    'gst_rate' => 18.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Clear', 'quantity' => 50],
                    ['color' => 'Smoke', 'quantity' => 30],
                ]
            ],
            [
                'product' => [
                    'name' => 'Tail Light',
                    'category_id' => 1,
                    'subcategory_id' => 1,
                    'price' => 450.00,
                    'hsn_code' => '85122000',
                    'gst_rate' => 18.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Red', 'quantity' => 75],
                    ['color' => 'Clear', 'quantity' => 25],
                ]
            ],
            [
                'product' => [
                    'name' => 'Floor Mat',
                    'category_id' => 1,
                    'subcategory_id' => 2,
                    'price' => 250.00,
                    'hsn_code' => '40169300',
                    'gst_rate' => 18.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Black', 'quantity' => 30],
                    ['color' => 'Grey', 'quantity' => 20],
                    ['color' => 'Brown', 'quantity' => 15],
                ]
            ],
            [
                'product' => [
                    'name' => 'Side Panel',
                    'category_id' => 1,
                    'subcategory_id' => 2,
                    'price' => 800.00,
                    'hsn_code' => '87089900',
                    'gst_rate' => 28.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'White', 'quantity' => 25],
                    ['color' => 'Black', 'quantity' => 20],
                    ['color' => 'Silver', 'quantity' => 15],
                    ['color' => 'Red', 'quantity' => 10],
                ]
            ],
            [
                'product' => [
                    'name' => 'Air Filter',
                    'category_id' => 1,
                    'subcategory_id' => 3,
                    'price' => 180.00,
                    'hsn_code' => '84213100',
                    'gst_rate' => 18.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'White', 'quantity' => 100],
                ]
            ],
            [
                'product' => [
                    'name' => 'Spark Plug',
                    'category_id' => 1,
                    'subcategory_id' => 3,
                    'price' => 120.00,
                    'hsn_code' => '85111000',
                    'gst_rate' => 18.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Silver', 'quantity' => 200],
                ]
            ],
            [
                'product' => [
                    'name' => 'Engine Oil',
                    'category_id' => 1,
                    'subcategory_id' => 3,
                    'price' => 350.00,
                    'hsn_code' => '27101981',
                    'gst_rate' => 28.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Golden', 'quantity' => 80],
                ]
            ],
            [
                'product' => [
                    'name' => 'Brake Pad Set',
                    'category_id' => 1,
                    'subcategory_id' => 4,
                    'price' => 280.00,
                    'hsn_code' => '87089900',
                    'gst_rate' => 28.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Black', 'quantity' => 60],
                ]
            ],
            [
                'product' => [
                    'name' => 'Chain Set',
                    'category_id' => 1,
                    'subcategory_id' => 3,
                    'price' => 650.00,
                    'hsn_code' => '87149900',
                    'gst_rate' => 28.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Silver', 'quantity' => 40],
                    ['color' => 'Gold', 'quantity' => 20],
                ]
            ],
            [
                'product' => [
                    'name' => 'Mirror Set',
                    'category_id' => 2,
                    'subcategory_id' => 7,
                    'price' => 180.00,
                    'hsn_code' => '87089900',
                    'gst_rate' => 28.00,
                    'is_composite' => false,
                ],
                'variants' => [
                    ['color' => 'Black', 'quantity' => 35],
                    ['color' => 'Chrome', 'quantity' => 25],
                ]
            ],
        ];

        foreach ($productsWithVariants as $productData) {
            // Create the main product (without color field)
            $product = Product::create($productData['product']);

            // Create color variants for this product
            foreach ($productData['variants'] as $variant) {
                ProductColorVariant::create([
                    'product_id' => $product->id,
                    'color' => $variant['color'],
                    'quantity' => $variant['quantity'],
                ]);
            }
        }

        // Create sample customers
        $customers = [
            [
                'name' => 'Rajesh Kumar',
                'mobile' => '9876543210',
                'address' => '123, MG Road, Bangalore',
                'state' => 'Karnataka',
                'gstin' => '29ABCDE1234F1Z5',
                'email' => 'rajesh.kumar@email.com',
            ],
            [
                'name' => 'Priya Sharma',
                'mobile' => '9876543211',
                'address' => '456, Park Street, Mumbai',
                'state' => 'Maharashtra',
                'gstin' => '27FGHIJ5678K2A6',
                'email' => 'priya.sharma@email.com',
            ],
            [
                'name' => 'Amit Patel',
                'mobile' => '9876543212',
                'address' => '789, Ring Road, Ahmedabad',
                'state' => 'Gujarat',
                'gstin' => '24LMNOP9012Q3B7',
                'email' => 'amit.patel@email.com',
            ],
            [
                'name' => 'Sunita Singh',
                'mobile' => '9876543213',
                'address' => '321, Civil Lines, Delhi',
                'state' => 'Delhi',
                'gstin' => '07RSTUV3456W4C8',
                'email' => 'sunita.singh@email.com',
            ],
            [
                'name' => 'Vikram Motors',
                'mobile' => '9876543214',
                'address' => '654, Industrial Area, Chennai',
                'state' => 'Tamil Nadu',
                'gstin' => '33XYZAB7890D5E9',
                'email' => 'info@vikrammotors.com',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
