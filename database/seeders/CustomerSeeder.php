<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Rajesh Kumar',
                'mobile' => '9876543210',
                'address' => '123, MG Road, Bangalore',
                'state' => 'Karnataka',
                'gstin' => '29ABCDE1234F1Z5',
                'email' => 'rajesh.kumar@email.com'
            ],
            [
                'name' => 'Priya Sharma',
                'mobile' => '9876543211',
                'address' => '456, Park Street, Mumbai',
                'state' => 'Maharashtra',
                'gstin' => '27FGHIJ5678K2A6',
                'email' => 'priya.sharma@email.com'
            ],
            [
                'name' => 'Amit Patel',
                'mobile' => '9876543212',
                'address' => '789, Ring Road, Ahmedabad',
                'state' => 'Gujarat',
                'gstin' => '24LMNOP9012Q3B7',
                'email' => 'amit.patel@email.com'
            ],
            [
                'name' => 'Sunita Singh',
                'mobile' => '9876543213',
                'address' => '321, Civil Lines, Delhi',
                'state' => 'Delhi',
                'gstin' => '07RSTUV3456W4C8',
                'email' => 'sunita.singh@email.com'
            ],
            [
                'name' => 'Vikram Motors',
                'mobile' => '9876543214',
                'address' => '654, Industrial Area, Chennai',
                'state' => 'Tamil Nadu',
                'gstin' => '33XYZAB7890D5E9',
                'email' => 'info@vikrammotors.com'
            ]
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
