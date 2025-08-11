<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'almikailautomobiles@gmail.com',
            'password' => Hash::make('Almikail@786'),
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

        // Product seeding has been removed as requested

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
