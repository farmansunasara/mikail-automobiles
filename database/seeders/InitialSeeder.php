<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSeeder extends Seeder
{
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
    }
}
