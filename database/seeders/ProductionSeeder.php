<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@mikailautomobiles.com',
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
            ['category_id' => 1, 'name' => 'Electrical'],
            ['category_id' => 1, 'name' => 'Body Parts'],
            ['category_id' => 1, 'name' => 'Engine Parts'],
            ['category_id' => 1, 'name' => 'Brake System'],
            ['category_id' => 1, 'name' => 'Suspension'],
            ['category_id' => 2, 'name' => 'Electrical'],
            ['category_id' => 2, 'name' => 'Body Parts'],
            ['category_id' => 2, 'name' => 'Engine Parts'],
            ['category_id' => 2, 'name' => 'Brake System'],
            ['category_id' => 3, 'name' => 'Electrical'],
            ['category_id' => 3, 'name' => 'Body Parts'],
            ['category_id' => 3, 'name' => 'Engine Parts'],
            ['category_id' => 3, 'name' => 'Transmission'],
            ['category_id' => 4, 'name' => 'Electrical'],
            ['category_id' => 4, 'name' => 'Body Parts'],
            ['category_id' => 4, 'name' => 'Engine Parts'],
            ['category_id' => 4, 'name' => 'Fuel System'],
            ['category_id' => 5, 'name' => 'Electrical'],
            ['category_id' => 5, 'name' => 'Body Parts'],
            ['category_id' => 5, 'name' => 'Engine Parts'],
            ['category_id' => 5, 'name' => 'Clutch System'],
        ];

        foreach ($subcategories as $subcategoryData) {
            Subcategory::create($subcategoryData);
        }

        // Create sample products
        $products = [
            [
                'name' => 'Headlight Assembly',
                'category_id' => 1,
                'subcategory_id' => 1,
                'color' => 'Clear',
                'quantity' => 50,
                'price' => 1200.00,
                'hsn_code' => '85122000',
                'gst_rate' => 18.00,
                'is_composite' => false,
            ],
            [
                'name' => 'Tail Light',
                'category_id' => 1,
                'subcategory_id' => 1,
                'color' => 'Red',
                'quantity' => 75,
                'price' => 450.00,
                'hsn_code' => '85122000',
                'gst_rate' => 18.00,
                'is_composite' => false,
            ],
            [
                'name' => 'Floor Mat',
                'category_id' => 1,
                'subcategory_id' => 2,
                'color' => 'Black',
                'quantity' => 30,
                'price' => 250.00,
                'hsn_code' => '40169300',
                'gst_rate' => 18.00,
                'is_composite' => false,
            ],
            [
                'name' => 'Side Panel Left',
                'category_id' => 1,
                'subcategory_id' => 2,
                'color' => 'White',
                'quantity' => 25,
                'price' => 800.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false,
            ],
            [
                'name' => 'Side Panel Right',
                'category_id' => 1,
                'subcategory_id' => 2,
                'color' => 'White',
                'quantity' => 25,
                'price' => 800.00,
                'hsn_code' => '87089900',
                'gst_rate' => 28.00,
                'is_composite' => false,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
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
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
