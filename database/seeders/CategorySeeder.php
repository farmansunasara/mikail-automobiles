<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Honda Activa 6G',
                'description' => 'Parts for Honda Activa 6G',
                'subcategories' => ['Electrical', 'Body Parts', 'Engine Parts', 'Brake System', 'Suspension']
            ],
            [
                'name' => 'Honda Activa 5G',
                'description' => 'Parts for Honda Activa 5G',
                'subcategories' => ['Electrical', 'Body Parts', 'Engine Parts', 'Brake System']
            ],
            [
                'name' => 'TVS Jupiter',
                'description' => 'Parts for TVS Jupiter',
                'subcategories' => ['Electrical', 'Body Parts', 'Engine Parts', 'Transmission']
            ],
            [
                'name' => 'Bajaj Pulsar',
                'description' => 'Parts for Bajaj Pulsar',
                'subcategories' => ['Electrical', 'Body Parts', 'Engine Parts', 'Fuel System']
            ],
            [
                'name' => 'Hero Splendor',
                'description' => 'Parts for Hero Splendor',
                'subcategories' => ['Electrical', 'Body Parts', 'Engine Parts', 'Clutch System']
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description']
            ]);

            foreach ($categoryData['subcategories'] as $subcategoryName) {
                Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName
                ]);
            }
        }
    }
}
